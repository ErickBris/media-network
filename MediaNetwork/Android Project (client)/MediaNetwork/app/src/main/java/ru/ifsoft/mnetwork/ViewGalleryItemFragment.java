package ru.ifsoft.mnetwork;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.widget.SwipeRefreshLayout;
import android.util.Log;
import android.view.KeyEvent;
import android.view.LayoutInflater;
import android.view.Menu;
import android.view.MenuInflater;
import android.view.MenuItem;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.InputMethodManager;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ListView;
import android.widget.PopupWindow;
import android.widget.RelativeLayout;
import android.widget.TextView;
import android.widget.Toast;

import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.RetryPolicy;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.ImageLoader;
import com.pkmmte.view.CircularImageView;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;

import github.ankushsachdeva.emojicon.EditTextImeBackListener;
import github.ankushsachdeva.emojicon.EmojiconEditText;
import github.ankushsachdeva.emojicon.EmojiconGridView;
import github.ankushsachdeva.emojicon.EmojiconsPopup;
import github.ankushsachdeva.emojicon.emoji.Emojicon;
import ru.ifsoft.mnetwork.adapter.CommentListAdapter;
import ru.ifsoft.mnetwork.app.App;
import ru.ifsoft.mnetwork.constants.Constants;
import ru.ifsoft.mnetwork.dialogs.CommentActionDialog;
import ru.ifsoft.mnetwork.dialogs.CommentDeleteDialog;
import ru.ifsoft.mnetwork.dialogs.ItemDeleteDialog;
import ru.ifsoft.mnetwork.dialogs.ItemReportDialog;
import ru.ifsoft.mnetwork.model.Comment;
import ru.ifsoft.mnetwork.model.GalleryItem;
import ru.ifsoft.mnetwork.util.Api;
import ru.ifsoft.mnetwork.util.CommentInterface;
import ru.ifsoft.mnetwork.util.CustomRequest;
import ru.ifsoft.mnetwork.view.ResizableImageView;


public class ViewGalleryItemFragment extends Fragment implements Constants, SwipeRefreshLayout.OnRefreshListener, CommentInterface {

    private ProgressDialog pDialog;

    SwipeRefreshLayout mContentContainer;
    RelativeLayout mErrorScreen, mLoadingScreen, mEmptyScreen;
    LinearLayout mContentScreen, mCommentFormContainer, mItemLocationContainer;

    EmojiconEditText mCommentText;

    ListView listView;
    Button mRetryBtn;

    View mListViewHeader;

    ImageView mItemLike, mEmojiBtn, mSendComment, mItemAuthorOnlineIcon;
    TextView mItemCity, mItemCountry, mItemAuthor, mItemUsername, mItemText, mItemTimeAgo, mItemLikesCount;
    ResizableImageView mItemImg;
    CircularImageView mItemAuthorPhoto, mItemAuthorIcon;

    ImageView mItemPlay;

    LinearLayout mItemHeader, mItemContent, mItemFooter;

    ImageLoader imageLoader = App.getInstance().getImageLoader();


    private ArrayList<Comment> commentsList;

    private CommentListAdapter itemAdapter;

    GalleryItem item;

    long itemId = 0, replyToUserId = 0;
    int arrayLength = 0;
    String commentText = "", imgUrl = "";

    private Boolean loading = false;
    private Boolean restore = false;
    private Boolean preload = false;
    private Boolean loadingComplete = false;

    EmojiconsPopup popup;

    public ViewGalleryItemFragment() {
        // Required empty public constructor
    }

    @Override
    public void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);

        setRetainInstance(true);

        setHasOptionsMenu(true);

        initpDialog();

        Intent i = getActivity().getIntent();

        itemId = i.getLongExtra("itemId", 0);

        commentsList = new ArrayList<>();
        itemAdapter = new CommentListAdapter(getActivity(), commentsList, this);
    }

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {

        View rootView = inflater.inflate(R.layout.fragment_view_gallery_item, container, false);

        popup = new EmojiconsPopup(rootView, getActivity());

        popup.setSizeForSoftKeyboard();

        popup.setOnEmojiconClickedListener(new EmojiconGridView.OnEmojiconClickedListener() {

            @Override
            public void onEmojiconClicked(Emojicon emojicon) {

                mCommentText.append(emojicon.getEmoji());
            }
        });

        popup.setOnEmojiconBackspaceClickedListener(new EmojiconsPopup.OnEmojiconBackspaceClickedListener() {

            @Override
            public void onEmojiconBackspaceClicked(View v) {

                KeyEvent event = new KeyEvent(0, 0, 0, KeyEvent.KEYCODE_DEL, 0, 0, 0, 0, KeyEvent.KEYCODE_ENDCALL);
                mCommentText.dispatchKeyEvent(event);
            }
        });

        popup.setOnDismissListener(new PopupWindow.OnDismissListener() {

            @Override
            public void onDismiss() {

                setIconEmojiKeyboard();
            }
        });

        popup.setOnSoftKeyboardOpenCloseListener(new EmojiconsPopup.OnSoftKeyboardOpenCloseListener() {

            @Override
            public void onKeyboardOpen(int keyBoardHeight) {

            }

            @Override
            public void onKeyboardClose() {

                if (popup.isShowing())

                    popup.dismiss();
            }
        });

        popup.setOnEmojiconClickedListener(new EmojiconGridView.OnEmojiconClickedListener() {

            @Override
            public void onEmojiconClicked(Emojicon emojicon) {

                mCommentText.append(emojicon.getEmoji());
            }
        });

        popup.setOnEmojiconBackspaceClickedListener(new EmojiconsPopup.OnEmojiconBackspaceClickedListener() {

            @Override
            public void onEmojiconBackspaceClicked(View v) {

                KeyEvent event = new KeyEvent(0, 0, 0, KeyEvent.KEYCODE_DEL, 0, 0, 0, 0, KeyEvent.KEYCODE_ENDCALL);
                mCommentText.dispatchKeyEvent(event);
            }
        });

        if (savedInstanceState != null) {

            restore = savedInstanceState.getBoolean("restore");
            loading = savedInstanceState.getBoolean("loading");
            preload = savedInstanceState.getBoolean("preload");

            replyToUserId = savedInstanceState.getLong("replyToUserId");

        } else {

            restore = false;
            loading = false;
            preload = false;

            replyToUserId = 0;
        }

        if (loading) {

            showpDialog();
        }

        mEmptyScreen = (RelativeLayout) rootView.findViewById(R.id.emptyScreen);
        mErrorScreen = (RelativeLayout) rootView.findViewById(R.id.errorScreen);
        mLoadingScreen = (RelativeLayout) rootView.findViewById(R.id.loadingScreen);
        mContentContainer = (SwipeRefreshLayout) rootView.findViewById(R.id.contentContainer);
        mContentContainer.setOnRefreshListener(this);

        mContentScreen = (LinearLayout) rootView.findViewById(R.id.contentScreen);
        mCommentFormContainer = (LinearLayout) rootView.findViewById(R.id.commentFormContainer);

        mCommentText = (EmojiconEditText) rootView.findViewById(R.id.commentText);
        mSendComment = (ImageView) rootView.findViewById(R.id.sendCommentImg);
        mEmojiBtn = (ImageView) rootView.findViewById(R.id.emojiBtn);

        mSendComment.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {

                send();
            }
        });

        mRetryBtn = (Button) rootView.findViewById(R.id.retryBtn);

        mRetryBtn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {

                if (App.getInstance().isConnected()) {

                    showLoadingScreen();

                    getItem();
                }
            }
        });

        listView = (ListView) rootView.findViewById(R.id.listView);

        mListViewHeader = getActivity().getLayoutInflater().inflate(R.layout.view_gallery_item_header, null);

        listView.addHeaderView(mListViewHeader);

        listView.setAdapter(itemAdapter);

        mItemAuthorPhoto = (CircularImageView) mListViewHeader.findViewById(R.id.itemAuthorPhoto);
        mItemAuthorIcon = (CircularImageView) mListViewHeader.findViewById(R.id.itemAuthorIcon);
        mItemLike = (ImageView) mListViewHeader.findViewById(R.id.itemLike);

        mItemAuthor = (TextView) mListViewHeader.findViewById(R.id.itemAuthor);
        mItemAuthorOnlineIcon = (ImageView) mListViewHeader.findViewById(R.id.itemAuthorOnlineIcon);
        mItemUsername = (TextView) mListViewHeader.findViewById(R.id.itemUsername);
        mItemText = (TextView) mListViewHeader.findViewById(R.id.itemText);
        mItemTimeAgo = (TextView) mListViewHeader.findViewById(R.id.itemTimeAgo);
        mItemLikesCount = (TextView) mListViewHeader.findViewById(R.id.itemLikesCount);
        mItemCity = (TextView) mListViewHeader.findViewById(R.id.itemCity);
        mItemCountry = (TextView) mListViewHeader.findViewById(R.id.itemCountry);
        mItemLocationContainer = (LinearLayout) mListViewHeader.findViewById(R.id.locationContainer);
        mItemImg = (ResizableImageView) mListViewHeader.findViewById(R.id.itemImg);

        mItemHeader = (LinearLayout) mListViewHeader.findViewById(R.id.itemHeader);
        mItemContent = (LinearLayout) mListViewHeader.findViewById(R.id.itemContent);
        mItemFooter = (LinearLayout) mListViewHeader.findViewById(R.id.itemFooter);

        mItemPlay = (ImageView) mListViewHeader.findViewById(R.id.itemPlay);

        if (App.getInstance().getSettings().getAllowEmoji() == DISABLED) {

            mEmojiBtn.setVisibility(View.GONE);
        }

        mEmojiBtn.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {

                if (!popup.isShowing()) {

                    if (popup.isKeyBoardOpen()){

                        popup.showAtBottom();
                        setIconSoftKeyboard();

                    } else {

                        mCommentText.setFocusableInTouchMode(true);
                        mCommentText.requestFocus();
                        popup.showAtBottomPending();

                        final InputMethodManager inputMethodManager = (InputMethodManager) getActivity().getSystemService(Context.INPUT_METHOD_SERVICE);
                        inputMethodManager.showSoftInput(mCommentText, InputMethodManager.SHOW_IMPLICIT);
                        setIconSoftKeyboard();
                    }

                } else {

                    popup.dismiss();
                }
            }
        });

        EditTextImeBackListener er = new EditTextImeBackListener() {

            @Override
            public void onImeBack(EmojiconEditText ctrl, String text) {

                hideEmojiKeyboard();
            }
        };

        mCommentText.setOnEditTextImeBackListener(er);

        if (!restore) {

            if (App.getInstance().isConnected()) {

                showLoadingScreen();
                getItem();

            } else {

                showErrorScreen();
            }

        } else {

            if (App.getInstance().isConnected()) {

                if (!preload) {

                    loadingComplete();
                    updateItem();

                } else {

                    showLoadingScreen();
                }

            } else {

                showErrorScreen();
            }
        }

        // Inflate the layout for this fragment
        return rootView;
    }

    public void hideEmojiKeyboard() {

        popup.dismiss();
    }

    public void setIconEmojiKeyboard() {

        mEmojiBtn.setBackgroundResource(R.drawable.ic_emoji);
    }

    public void setIconSoftKeyboard() {

        mEmojiBtn.setBackgroundResource(R.drawable.ic_keyboard);
    }

    public void onDestroyView() {

        super.onDestroyView();

        hidepDialog();
    }

    protected void initpDialog() {

        pDialog = new ProgressDialog(getActivity());
        pDialog.setMessage(getString(R.string.msg_loading));
        pDialog.setCancelable(false);
    }

    protected void showpDialog() {

        if (!pDialog.isShowing()) pDialog.show();
    }

    protected void hidepDialog() {

        if (pDialog.isShowing()) pDialog.dismiss();
    }

    @Override
    public void onSaveInstanceState(Bundle outState) {

        super.onSaveInstanceState(outState);

        outState.putBoolean("restore", true);
        outState.putBoolean("loading", loading);
        outState.putBoolean("preload", preload);

        outState.putLong("replyToUserId", replyToUserId);
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {

        super.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    public void onRefresh() {

        if (App.getInstance().isConnected()) {

            mContentContainer.setRefreshing(true);
            getItem();

        } else {

            mContentContainer.setRefreshing(false);
        }
    }

    public void updateItem() {

        if (imageLoader == null) {

            imageLoader = App.getInstance().getImageLoader();
        }

        if (item.getItemType() == Constants.GALLERY_ITEM_TYPE_VIDEO) {

            mItemPlay.setVisibility(View.VISIBLE);

//            mItemImg.getLayoutParams().height = 512;
//            mItemImg.setScaleType(ImageView.ScaleType.CENTER_CROP);
//            mItemImg.requestLayout();

        } else {

            mItemPlay.setVisibility(View.GONE);
        }

        if ((item.getCity() != null && item.getCity().length() > 0) || (item.getCountry() != null && item.getCountry().length() > 0)) {

            if (item.getCity() != null && item.getCity().length() > 0) {

                mItemCity.setText(item.getCity());
                mItemCity.setVisibility(View.VISIBLE);

            } else {

                mItemCity.setVisibility(View.GONE);
            }

            if (item.getCountry() != null && item.getCountry().length() > 0) {

                mItemCountry.setText(item.getCountry());
                mItemCountry.setVisibility(View.VISIBLE);

            } else {

                mItemCountry.setVisibility(View.GONE);
            }

            mItemLocationContainer.setVisibility(View.VISIBLE);

        } else {

            mItemLocationContainer.setVisibility(View.GONE);
        }

        mItemAuthor.setText(item.getFromUserFullname());
        mItemUsername.setText("@" + item.getFromUserUsername());

        if (item.getFromUserOnline() && item.getFromUserAllowShowOnline() == 1) {

            mItemAuthorOnlineIcon.setVisibility(View.VISIBLE);

        } else {

            mItemAuthorOnlineIcon.setVisibility(View.GONE);
        }

        if (item.getFromUserPhotoUrl().length() != 0) {

            mItemAuthorPhoto.setVisibility(View.VISIBLE);

            imageLoader.get(item.getFromUserPhotoUrl(), ImageLoader.getImageListener(mItemAuthorPhoto, R.drawable.profile_default_photo, R.drawable.profile_default_photo));

        } else {

            mItemAuthorPhoto.setVisibility(View.VISIBLE);
            mItemAuthorPhoto.setImageResource(R.drawable.profile_default_photo);
        }

        if (item.getFromUserVerified() == 1) {

            mItemAuthorIcon.setVisibility(View.VISIBLE);

        } else {

            mItemAuthorIcon.setVisibility(View.GONE);
        }

        mItemAuthorPhoto.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {

                Intent intent = new Intent(getActivity(), ProfileActivity.class);
                intent.putExtra("profileId", item.getFromUserId());
                startActivity(intent);
            }
        });

        mItemAuthor.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {

                Intent intent = new Intent(getActivity(), ProfileActivity.class);
                intent.putExtra("profileId", item.getFromUserId());
                startActivity(intent);
            }
        });

        if (item.getFromUserId() == App.getInstance().getId()) {

            itemAdapter.setMyPost(true);

        } else {

            itemAdapter.setMyPost(false);
        }

        mItemLike.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {

                if (App.getInstance().isConnected()) {

                    CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_LIKE_ADD, null,
                            new Response.Listener<JSONObject>() {
                                @Override
                                public void onResponse(JSONObject response) {

                                    try {

                                        if (!response.getBoolean("error")) {

                                            item.setLikesCount(response.getInt("likesCount"));
                                            item.setMyLike(response.getBoolean("myLike"));
                                        }

                                    } catch (JSONException e) {

                                        e.printStackTrace();

                                    } finally {

                                        updateItem();

                                        Log.e("", response.toString());
                                    }
                                }
                            }, new Response.ErrorListener() {
                        @Override
                        public void onErrorResponse(VolleyError error) {

                            Log.e("Gallery.Like", error.toString());
                        }
                    }) {

                        @Override
                        protected Map<String, String> getParams() {
                            Map<String, String> params = new HashMap<String, String>();
                            params.put("accountId", Long.toString(App.getInstance().getId()));
                            params.put("accessToken", App.getInstance().getAccessToken());
                            params.put("itemId", Long.toString(item.getId()));
                            params.put("itemFromUserId", Long.toString(item.getFromUserId()));
                            params.put("itemType", Integer.toString(ITEM_TYPE_GALLERY_ITEM));

                            return params;
                        }
                    };

                    App.getInstance().addToRequestQueue(jsonReq);

                } else {

                    Toast.makeText(getActivity(), getText(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
                }
            }
        });

        if (item.isMyLike()) {

            mItemLike.setImageResource(R.drawable.perk_active);

        } else {

            mItemLike.setImageResource(R.drawable.perk);
        }

        if (item.getLikesCount() > 0) {

            mItemLikesCount.setText(Integer.toString(item.getLikesCount()));
            mItemLikesCount.setVisibility(View.VISIBLE);

        } else {

            mItemLikesCount.setText(Integer.toString(item.getLikesCount()));
            mItemLikesCount.setVisibility(View.GONE);
        }

        mItemLikesCount.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {

                Intent intent = new Intent(getActivity(), LikersActivity.class);
                intent.putExtra("itemId", item.getId());
                intent.putExtra("itemType", ITEM_TYPE_GALLERY_ITEM);
                startActivity(intent);
            }
        });


        mItemTimeAgo.setText(item.getTimeAgo());
        mItemTimeAgo.setVisibility(View.VISIBLE);

        if (item.getDescription().length() > 0) {

            mItemText.setText(item.getDescription().replaceAll("<br>", "\n"));

            mItemText.setVisibility(View.VISIBLE);

        } else {

            mItemText.setVisibility(View.GONE);
        }

        if (item.getImgUrl().length() > 0) {

            imageLoader.get(item.getImgUrl(), ImageLoader.getImageListener(mItemImg, R.drawable.img_loading, R.drawable.img_loading));
            mItemImg.setVisibility(View.VISIBLE);

            mItemImg.setOnClickListener(new View.OnClickListener() {

                @Override
                public void onClick(View v) {

                    if (item.getItemType() == Constants.GALLERY_ITEM_TYPE_VIDEO) {

                        playVideo(item.getVideoUrl());

                    } else {

                        Intent i = new Intent(getActivity(), PhotoViewActivity.class);
                        i.putExtra("imgUrl", item.getImgUrl());
                        startActivity(i);
                    }
                }
            });

        } else {

            mItemImg.setVisibility(View.GONE);
        }

        mItemPlay.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {

                playVideo(item.getVideoUrl());
            }
        });
    }

    public void playVideo(String videoUrl) {

        Intent i = new Intent(getActivity(), VideoPlayActivity.class);
        i.putExtra("videoUrl", videoUrl);
        startActivity(i);
    }

    public void getItem() {

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_GALLERY_GET_ITEM, null,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {

                        try {

                            arrayLength = 0;

                            if (!response.getBoolean("error")) {

                                commentsList.clear();

                                itemId = response.getInt("itemId");

                                if (response.has("items")) {

                                    JSONArray itemsArray = response.getJSONArray("items");

                                    arrayLength = itemsArray.length();

                                    if (arrayLength > 0) {

                                        for (int i = 0; i < itemsArray.length(); i++) {

                                            JSONObject itemObj = (JSONObject) itemsArray.get(i);

                                            item = new GalleryItem(itemObj);

                                            updateItem();
                                        }
                                    }
                                }

                                if (response.has("comments") && item.getFromUserAllowGalleryComments() == 1) {

                                    JSONObject commentsObj = response.getJSONObject("comments");

                                    if (commentsObj.has("comments")) {

                                        JSONArray commentsArray = commentsObj.getJSONArray("comments");

                                        arrayLength = commentsArray.length();

                                        if (arrayLength > 0) {

                                            for (int i = commentsArray.length() - 1; i > -1 ; i--) {

                                                JSONObject itemObj = (JSONObject) commentsArray.get(i);

                                                Comment comment = new Comment(itemObj);

                                                commentsList.add(comment);
                                            }
                                        }
                                    }
                                }

                                loadingComplete();

                            } else {

                                showErrorScreen();
                            }

                        } catch (JSONException e) {

                            showErrorScreen();

                            e.printStackTrace();
                        }
                    }
                }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

                showErrorScreen();
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());
                params.put("itemId", Long.toString(itemId));
                params.put("language", "en");

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }

    public void send() {

        commentText = mCommentText.getText().toString();
        commentText = commentText.trim();

        if (App.getInstance().isConnected() && commentText.length() > 0) {

            loading = true;

            showpDialog();

            CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_COMMENT_ADD, null,
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject response) {

                            try {

                                if (!response.getBoolean("error")) {

                                    if (response.has("comment")) {

                                        JSONObject commentObj = (JSONObject) response.getJSONObject("comment");

                                        Comment comment = new Comment(commentObj);

                                        commentsList.add(comment);

                                        itemAdapter.notifyDataSetChanged();

                                        listView.setSelection(itemAdapter.getCount() - 1);

                                        mCommentText.setText("");
                                        replyToUserId = 0;
                                    }

                                    Toast.makeText(getActivity(), getString(R.string.msg_comment_has_been_added), Toast.LENGTH_SHORT).show();

                                }

                            } catch (JSONException e) {

                                e.printStackTrace();

                            } finally {

                                loading = false;

                                hidepDialog();

                                Log.e("Comment.add (Response)", response.toString());
                            }
                        }
                    }, new Response.ErrorListener() {
                @Override
                public void onErrorResponse(VolleyError error) {

                    loading = false;

                    hidepDialog();

                    Log.e("Comment.add", error.toString());
                }
            }) {

                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<String, String>();
                    params.put("accountId", Long.toString(App.getInstance().getId()));
                    params.put("accessToken", App.getInstance().getAccessToken());

                    params.put("itemId", Long.toString(item.getId()));
                    params.put("itemFromUserId", Long.toString(item.getFromUserId()));
                    params.put("content", commentText);
                    params.put("imgUrl", imgUrl);
                    params.put("itemType", Integer.toString(ITEM_TYPE_GALLERY_ITEM));

                    params.put("replyToUserId", Long.toString(replyToUserId));

                    return params;
                }
            };

            int socketTimeout = 0;//0 seconds - change to what you want
            RetryPolicy policy = new DefaultRetryPolicy(socketTimeout, 0, DefaultRetryPolicy.DEFAULT_BACKOFF_MULT);

            jsonReq.setRetryPolicy(policy);

            App.getInstance().addToRequestQueue(jsonReq);
        }
    }

    public void onItemDelete(final int position) {

        Api api = new Api(getActivity());

        api.galleryItemDelete(item.getId());

        getActivity().finish();
    }

    public void onItemReport(int position, int reasonId) {

        if (App.getInstance().isConnected()) {

            Api api = new Api(getActivity());

            api.report(item.getId(), ITEM_TYPE_GALLERY_ITEM, position);

        } else {

            Toast.makeText(getActivity(), getText(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
        }
    }

    public void remove(int position) {

        android.app.FragmentManager fm = getActivity().getFragmentManager();

        ItemDeleteDialog alert = new ItemDeleteDialog();

        Bundle b = new Bundle();
        b.putInt("position", 0);

        alert.setArguments(b);
        alert.show(fm, "alert_dialog_photo_delete");
    }

    public void report(int position) {

        android.app.FragmentManager fm = getActivity().getFragmentManager();

        ItemReportDialog alert = new ItemReportDialog();

        Bundle b  = new Bundle();
        b.putInt("position", position);
        b.putInt("reason", 0);

        alert.setArguments(b);
        alert.show(fm, "alert_dialog_photo_report");
    }

    public void loadingComplete() {

        itemAdapter.notifyDataSetChanged();

        if (listView.getAdapter().getCount() == 0) {

            showEmptyScreen();

        } else {

            showContentScreen();
        }

        if (mContentContainer.isRefreshing()) {

            mContentContainer.setRefreshing(false);
        }
    }

    public void showLoadingScreen() {

        preload = true;

        mContentScreen.setVisibility(View.GONE);
        mErrorScreen.setVisibility(View.GONE);
        mEmptyScreen.setVisibility(View.GONE);

        mLoadingScreen.setVisibility(View.VISIBLE);

        loadingComplete = false;
    }

    public void showEmptyScreen() {

        mContentScreen.setVisibility(View.GONE);
        mLoadingScreen.setVisibility(View.GONE);
        mErrorScreen.setVisibility(View.GONE);

        mEmptyScreen.setVisibility(View.VISIBLE);

        loadingComplete = false;
    }

    public void showErrorScreen() {

        mContentScreen.setVisibility(View.GONE);
        mLoadingScreen.setVisibility(View.GONE);
        mEmptyScreen.setVisibility(View.GONE);

        mErrorScreen.setVisibility(View.VISIBLE);

        loadingComplete = false;
    }

    public void showContentScreen() {

        preload = false;

        mLoadingScreen.setVisibility(View.GONE);
        mErrorScreen.setVisibility(View.GONE);
        mEmptyScreen.setVisibility(View.GONE);

        mContentScreen.setVisibility(View.VISIBLE);

        if (item.getFromUserAllowGalleryComments() == COMMENTS_DISABLED) {

            mCommentFormContainer.setVisibility(View.GONE);
        }

        loadingComplete = true;

        getActivity().invalidateOptionsMenu();
    }

    @Override
    public void onAttach(Activity activity) {
        super.onAttach(activity);
    }

    @Override
    public void onDetach() {
        super.onDetach();
    }

    public void commentAction(int position) {

        final Comment comment = commentsList.get(position);

        /** Getting the fragment manager */
        android.app.FragmentManager fm = getActivity().getFragmentManager();

        /** Instantiating the DialogFragment class */
        CommentActionDialog alert = new CommentActionDialog();

        Bundle b  = new Bundle();

        /** Storing the selected item's index in the bundle object */
        b.putInt("position", position);
        b.putLong("commentFromUserId", comment.getFromUserId());
        b.putLong("itemFromUserId", item.getFromUserId());

        /** Setting the bundle object to the dialog fragment object */
        alert.setArguments(b);

        /** Creating the dialog fragment object, which will in turn open the alert dialog window */

        alert.show(fm, "alert_dialog_comment_action");
    }

    public void onCommentReply(final int position) {

        final Comment comment = commentsList.get(position);

        replyToUserId = comment.getFromUserId();

        mCommentText.setText("@" + comment.getFromUserUsername() + ", ");
        mCommentText.setSelection(mCommentText.getText().length());

        mCommentText.requestFocus();
    }

    public void onCommentRemove(int position) {

        /** Getting the fragment manager */
        android.app.FragmentManager fm = getActivity().getFragmentManager();

        /** Instantiating the DialogFragment class */
        CommentDeleteDialog alert = new CommentDeleteDialog();

        /** Creating a bundle object to store the selected item's index */
        Bundle b  = new Bundle();

        /** Storing the selected item's index in the bundle object */
        b.putInt("position", position);

        /** Setting the bundle object to the dialog fragment object */
        alert.setArguments(b);

        /** Creating the dialog fragment object, which will in turn open the alert dialog window */

        alert.show(fm, "alert_dialog_comment_delete");
    }

    public void onCommentDelete(final int position) {

        final Comment comment = commentsList.get(position);

        commentsList.remove(position);
        itemAdapter.notifyDataSetChanged();

        Api api = new Api(getActivity());

        api.commentDelete(comment.getId(), ITEM_TYPE_GALLERY_ITEM);
    }

    private void hideMenuItems(Menu menu, boolean visible) {

        for (int i = 0; i < menu.size(); i++){

            menu.getItem(i).setVisible(visible);
        }
    }

    @Override
    public void onCreateOptionsMenu(Menu menu, MenuInflater inflater) {

        super.onCreateOptionsMenu(menu, inflater);

        menu.clear();

        inflater.inflate(R.menu.menu_gallery_item, menu);
    }

    @Override
    public void onPrepareOptionsMenu(Menu menu) {

        super.onPrepareOptionsMenu(menu);

        if (loadingComplete) {

            menu.removeItem(R.id.action_edit);

            if (App.getInstance().getId() != item.getFromUserId()) {

                menu.removeItem(R.id.action_remove);

            } else {

                menu.removeItem(R.id.action_report);
            }

            //show all menu items
            hideMenuItems(menu, true);

        } else {

            //hide all menu items
            hideMenuItems(menu, false);
        }
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {

        switch (item.getItemId()) {

            case R.id.action_remove: {

                // remove item

                remove(0);

                return true;
            }

            case R.id.action_report: {

                // report item

                report(0);

                return true;
            }

            case R.id.action_share: {

                // share item

                Api api = new Api(getActivity());

                api.itemShare(this.item);

                return true;
            }

            default: {

                return super.onOptionsItemSelected(item);
            }
        }
    }
}