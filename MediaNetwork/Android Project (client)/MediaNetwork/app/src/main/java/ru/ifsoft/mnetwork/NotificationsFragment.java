package ru.ifsoft.mnetwork;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.widget.SwipeRefreshLayout;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AbsListView;
import android.widget.AdapterView;
import android.widget.ListView;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

import ru.ifsoft.mnetwork.adapter.NotifyListAdapter;
import ru.ifsoft.mnetwork.app.App;
import ru.ifsoft.mnetwork.common.FragmentBase;
import ru.ifsoft.mnetwork.dialogs.FriendRequestActionDialog;
import ru.ifsoft.mnetwork.model.Notify;
import ru.ifsoft.mnetwork.util.Api;
import ru.ifsoft.mnetwork.util.CustomRequest;

public class NotificationsFragment extends FragmentBase implements SwipeRefreshLayout.OnRefreshListener {

    private static final String STATE_LIST = "State Adapter Data";

    ListView mListView;
    TextView mMessage;

    SwipeRefreshLayout mItemsContainer;

    private ArrayList<Notify> notificationsList;
    private NotifyListAdapter notificationsAdapter;

    private int itemId = 0;
    private int arrayLength = 0;
    private Boolean loadingMore = false;
    private Boolean viewMore = false;
    private Boolean restore = false;

    private Boolean loadingComplete = false;

    public NotificationsFragment() {
        // Required empty public constructor
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);

        setHasOptionsMenu(true);

        if (savedInstanceState != null) {

            notificationsList = savedInstanceState.getParcelableArrayList(STATE_LIST);
            notificationsAdapter = new NotifyListAdapter(getActivity(), notificationsList);

            restore = savedInstanceState.getBoolean("restore");
            itemId = savedInstanceState.getInt("itemId");
            loadingComplete = savedInstanceState.getBoolean("loadingComplete");

        } else {

            notificationsList = new ArrayList<Notify>();
            notificationsAdapter = new NotifyListAdapter(getActivity(), notificationsList);

            restore = false;
            itemId = 0;
            loadingComplete = false;
        }
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        View rootView = inflater.inflate(R.layout.fragment_notifications, container, false);

        mItemsContainer = (SwipeRefreshLayout) rootView.findViewById(R.id.container_items);
        mItemsContainer.setOnRefreshListener(this);

        mMessage = (TextView) rootView.findViewById(R.id.message);

        mListView = (ListView) rootView.findViewById(R.id.listView);
        mListView.setAdapter(notificationsAdapter);

        if (notificationsAdapter.getCount() == 0) {

            showMessage(getText(R.string.label_empty_list).toString());

        } else {

            hideMessage();
        }

        mListView.setOnItemClickListener(new AdapterView.OnItemClickListener() {

            @Override
            public void onItemClick(AdapterView<?> adapterView, View view, int position, long l) {

                Notify notify = (Notify) adapterView.getItemAtPosition(position);

                switch (notify.getType()) {

                    case NOTIFY_TYPE_FRIEND_REQUEST: {

                        /** Getting the fragment manager */
                        android.app.FragmentManager fm = getActivity().getFragmentManager();

                        /** Instantiating the DialogFragment class */
                        FriendRequestActionDialog alert = new FriendRequestActionDialog();

                        /** Creating a bundle object to store the selected item's index */
                        Bundle b  = new Bundle();

                        /** Storing the selected item's index in the bundle object */
                        b.putInt("position", position);

                        /** Setting the bundle object to the dialog fragment object */
                        alert.setArguments(b);

                        /** Creating the dialog fragment object, which will in turn open the alert dialog window */

                        alert.show(fm, "alert_friend_request_action");

                        break;
                    }

                    default: {

                        Intent intent = new Intent(getActivity(), ViewGalleryItemActivity.class);
                        intent.putExtra("itemId", notify.getToItemId());
                        startActivity(intent);

                        break;
                    }
                }
            }
        });

        mListView.setOnScrollListener(new AbsListView.OnScrollListener() {

            @Override
            public void onScrollStateChanged(AbsListView view, int scrollState) {

            }

            @Override
            public void onScroll(AbsListView view, int firstVisibleItem, int visibleItemCount, int totalItemCount) {

                int lastInScreen = firstVisibleItem + visibleItemCount;

                if ((lastInScreen == totalItemCount) && !(loadingMore) && (viewMore) && !(mItemsContainer.isRefreshing())) {

                    if (App.getInstance().isConnected()) {

                        loadingMore = true;

                        getNotifications();
                    }
                }
            }
        });

        if (!restore) {

            showMessage(getText(R.string.msg_loading_2).toString());

            getNotifications();
        }


        // Inflate the layout for this fragment
        return rootView;
    }

    @Override
    public void onRefresh() {

        if (App.getInstance().isConnected()) {

            itemId = 0;
            getNotifications();

        } else {

            mItemsContainer.setRefreshing(false);
        }
    }

    @Override
    public void onSaveInstanceState(Bundle outState) {

        super.onSaveInstanceState(outState);

        outState.putBoolean("loadingComplete", true);
        outState.putBoolean("restore", true);
        outState.putInt("itemId", itemId);
        outState.putParcelableArrayList(STATE_LIST, notificationsList);
    }

    public void getNotifications() {

        mItemsContainer.setRefreshing(true);

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_NOTIFICATIONS_GET, null,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {

                        try {

                            arrayLength = 0;

                            if (!loadingMore) {

                                notificationsList.clear();
                            }

                            if (!response.getBoolean("error")) {

                                App.getInstance().setNotificationsCount(0);

                                itemId = response.getInt("itemId");

                                JSONArray notificationsArray = response.getJSONArray("items");

                                arrayLength = notificationsArray.length();

                                if (arrayLength > 0) {

                                    for (int i = 0; i < notificationsArray.length(); i++) {

                                        JSONObject notifyObj = (JSONObject) notificationsArray.get(i);

                                        Notify notify = new Notify(notifyObj);

                                        notificationsList.add(notify);
                                    }
                                }
                            }

                        } catch (JSONException e) {

                            e.printStackTrace();

                        } finally {

                            loadingComplete();

                            Log.d("Notifications.response", response.toString());
                        }
                    }
                }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

                loadingComplete();

                Log.d("Notifications.error", error.toString());
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());
                params.put("itemId", Integer.toString(itemId));

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }

    public void loadingComplete() {

        if (arrayLength == LIST_ITEMS) {

            viewMore = true;

        } else {

            viewMore = false;
        }

        notificationsAdapter.notifyDataSetChanged();

        if (notificationsAdapter.getCount() == 0) {

            showMessage(getText(R.string.label_empty_list).toString());

        } else {

            hideMessage();

            getActivity().invalidateOptionsMenu();
        }

        loadingMore = false;
        loadingComplete = true;
        mItemsContainer.setRefreshing(false);

        getActivity().invalidateOptionsMenu();
    }

    public void showMessage(String message) {

        mMessage.setText(message);
        mMessage.setVisibility(View.VISIBLE);
    }

    public void hideMessage() {

        mMessage.setVisibility(View.GONE);
    }

    public void onAcceptRequest(final int position) {

        final Notify item = notificationsList.get(position);

        notificationsList.remove(position);
        notificationsAdapter.notifyDataSetChanged();

        if (mListView.getAdapter().getCount() == 0) {

            showMessage(getText(R.string.label_empty_list).toString());

        } else {

            hideMessage();
        }

        if (App.getInstance().isConnected()) {

            Api api = new Api(getActivity());

            api.acceptFriendRequest(item.getFromUserId());

        } else {

            Toast.makeText(getActivity(), getText(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
        }
    }

    public void onRejectRequest(final int position) {

        final Notify item = notificationsList.get(position);

        notificationsList.remove(position);
        notificationsAdapter.notifyDataSetChanged();

        if (mListView.getAdapter().getCount() == 0) {

            showMessage(getText(R.string.label_empty_list).toString());

        } else {

            hideMessage();
        }

        if (App.getInstance().isConnected()) {

            Api api = new Api(getActivity());

            api.rejectFriendRequest(item.getFromUserId());

        } else {

            Toast.makeText(getActivity(), getText(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
        }
    }

    public void clear() {

        showpDialog();

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_NOTIFICATIONS_CLEAR, null,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {

                        try {

                            if (!response.getBoolean("error")) {

                                notificationsList.clear();

                                App.getInstance().setNotificationsCount(0);

                                itemId = 0;
                            }

                        } catch (JSONException e) {

                            e.printStackTrace();

                        } finally {

                            hidepDialog();

                            loadingComplete();

                            Log.d("Clear.response", response.toString());
                        }
                    }
                }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

                hidepDialog();

                loadingComplete();

                Log.d("Clear.error", error.toString());
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }

    @Override
    public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {

        super.onCreateOptionsMenu(menu, inflater);

        menu.clear();

        inflater.inflate(R.menu.menu_notifications, menu);
    }

    private void hideMenuItems(Menu menu, boolean visible) {

        for (int i = 0; i < menu.size(); i++){

            menu.getItem(i).setVisible(visible);
        }
    }

    @Override
    public void onPrepareOptionsMenu(Menu menu) {

        super.onPrepareOptionsMenu(menu);

        if (loadingComplete) {

            if (notificationsAdapter.getCount() == 0) {

                //show menu
                hideMenuItems(menu, false);

            } else {

                //hide menu
                hideMenuItems(menu, true);
            }

        } else {

            //hide all menu items
            hideMenuItems(menu, false);
        }
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {

        switch (item.getItemId()) {

            case R.id.action_remove_all: {

                // remove all notifications

                clear();

                return true;
            }

            default: {

                return super.onOptionsItemSelected(item);
            }
        }
    }

    @Override
    public void onAttach(Activity activity) {
        super.onAttach(activity);
    }

    @Override
    public void onDetach() {
        super.onDetach();
    }
}