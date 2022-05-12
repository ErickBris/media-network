package ru.ifsoft.mnetwork;

import android.app.ProgressDialog;
import android.os.Bundle;
import android.preference.CheckBoxPreference;
import android.preference.Preference;
import android.preference.PreferenceFragment;
import android.util.Log;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

import ru.ifsoft.mnetwork.app.App;
import ru.ifsoft.mnetwork.constants.Constants;
import ru.ifsoft.mnetwork.util.CustomRequest;

public class PrivacySettingsFragment extends PreferenceFragment implements Constants {

    private CheckBoxPreference mMessagesFromAnyone, mAllowGalleryComments, mAllowShowProfileOnlyToFriends, mAllowShowOnline, mAllowShowPhoneNumber;

    private ProgressDialog pDialog;

    int allowMessagesFromAnyone, allowGalleryComments, allowShowProfileOnlyToFriends, allowShowOnline, allowShowPhoneNumber;

    private Boolean loading = false;

    @Override
    public void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);

        setRetainInstance(true);

        initpDialog();

        // Load the preferences from an XML resource
        addPreferencesFromResource(R.xml.privacy_settings);

        mMessagesFromAnyone = (CheckBoxPreference) getPreferenceManager().findPreference("allowMessagesFromAnyone");

        mMessagesFromAnyone.setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {

            @Override
            public boolean onPreferenceChange(Preference preference, Object newValue) {

                if (newValue instanceof Boolean) {

                    Boolean value = (Boolean) newValue;

                    if (value) {

                        allowMessagesFromAnyone = 1;

                    } else {

                        allowMessagesFromAnyone = 0;
                    }

                    if (App.getInstance().isConnected()) {

                        savePrivacy_settings();

                    } else {

                        Toast.makeText(getActivity().getApplicationContext(), getText(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
                    }
                }

                return true;
            }
        });

        mAllowGalleryComments = (CheckBoxPreference) getPreferenceManager().findPreference("allowGalleryComments");

        mAllowGalleryComments.setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {

            @Override
            public boolean onPreferenceChange(Preference preference, Object newValue) {

                if (newValue instanceof Boolean) {

                    Boolean value = (Boolean) newValue;

                    if (value) {

                        allowGalleryComments = 1;

                    } else {

                        allowGalleryComments = 0;
                    }

                    if (App.getInstance().isConnected()) {

                        savePrivacy_settings();

                    } else {

                        Toast.makeText(getActivity().getApplicationContext(), getText(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
                    }
                }

                return true;
            }
        });

        mAllowShowProfileOnlyToFriends = (CheckBoxPreference) getPreferenceManager().findPreference("allowShowProfileOnlyToFriends");

        mAllowShowProfileOnlyToFriends.setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {

            @Override
            public boolean onPreferenceChange(Preference preference, Object newValue) {

                if (newValue instanceof Boolean) {

                    Boolean value = (Boolean) newValue;

                    if (value) {

                        allowShowProfileOnlyToFriends = 1;

                    } else {

                        allowShowProfileOnlyToFriends = 0;
                    }

                    if (App.getInstance().isConnected()) {

                        savePrivacy_settings();

                    } else {

                        Toast.makeText(getActivity().getApplicationContext(), getText(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
                    }
                }

                return true;
            }
        });

        mAllowShowOnline = (CheckBoxPreference) getPreferenceManager().findPreference("allowShowOnline");

        mAllowShowOnline.setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {

            @Override
            public boolean onPreferenceChange(Preference preference, Object newValue) {

                if (newValue instanceof Boolean) {

                    Boolean value = (Boolean) newValue;

                    if (value) {

                        allowShowOnline = 1;

                    } else {

                        allowShowOnline = 0;
                    }

                    if (App.getInstance().isConnected()) {

                        savePrivacy_settings();

                    } else {

                        Toast.makeText(getActivity().getApplicationContext(), getText(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
                    }
                }

                return true;
            }
        });

        mAllowShowPhoneNumber = (CheckBoxPreference) getPreferenceManager().findPreference("allowShowPhoneNumber");

        mAllowShowPhoneNumber.setOnPreferenceChangeListener(new Preference.OnPreferenceChangeListener() {

            @Override
            public boolean onPreferenceChange(Preference preference, Object newValue) {

                if (newValue instanceof Boolean) {

                    Boolean value = (Boolean) newValue;

                    if (value) {

                        allowShowPhoneNumber = 1;

                    } else {

                        allowShowPhoneNumber = 0;
                    }

                    if (App.getInstance().isConnected()) {

                        savePrivacy_settings();

                    } else {

                        Toast.makeText(getActivity().getApplicationContext(), getText(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
                    }
                }

                return true;
            }
        });

        checkAllowMessagesFromAnyone(App.getInstance().getAllowMessagesFromAnyone());
        checkAllowGalleryComments(App.getInstance().getAllowGalleryComments());
        checkAllowShowProfileOnlyToFriends(App.getInstance().getAllowShowProfileOnlyToFriends());
        checkAllowShowOnline(App.getInstance().getAllowShowOnline());
        checkAllowShowPhoneNumber(App.getInstance().getAllowShowPhoneNumber());
    }

    public void onActivityCreated(Bundle savedInstanceState) {

        super.onActivityCreated(savedInstanceState);

        if (savedInstanceState != null) {

            loading = savedInstanceState.getBoolean("loading");

        } else {

            loading = false;
        }

        if (loading) {

            showpDialog();
        }
    }

    public void onDestroyView() {

        super.onDestroyView();

        hidepDialog();
    }

    @Override
    public void onSaveInstanceState(Bundle outState) {

        super.onSaveInstanceState(outState);

        outState.putBoolean("loading", loading);
    }

    public void checkAllowMessagesFromAnyone(int value) {

        if (value == 1) {

            mMessagesFromAnyone.setChecked(true);
            allowMessagesFromAnyone = 1;

        } else {

            mMessagesFromAnyone.setChecked(false);
            allowMessagesFromAnyone = 0;
        }
    }

    public void checkAllowGalleryComments(int value) {

        if (value == 1) {

            mAllowGalleryComments.setChecked(true);
            allowGalleryComments = 1;

        } else {

            mAllowGalleryComments.setChecked(false);
            allowGalleryComments = 0;
        }
    }

    public void checkAllowShowProfileOnlyToFriends(int value) {

        if (value == 1) {

            mAllowShowProfileOnlyToFriends.setChecked(true);
            allowShowProfileOnlyToFriends = 1;

        } else {

            mAllowShowProfileOnlyToFriends.setChecked(false);
            allowShowProfileOnlyToFriends = 0;
        }
    }

    public void checkAllowShowOnline(int value) {

        if (value == 1) {

            mAllowShowOnline.setChecked(true);
            allowShowOnline = 1;

        } else {

            mAllowShowOnline.setChecked(false);
            allowShowOnline = 0;
        }
    }

    public void checkAllowShowPhoneNumber(int value) {

        if (value == 1) {

            mAllowShowPhoneNumber.setChecked(true);
            allowShowPhoneNumber = 1;

        } else {

            mAllowShowPhoneNumber.setChecked(false);
            allowShowPhoneNumber = 0;
        }
    }

    public void savePrivacy_settings() {

        loading = true;

        showpDialog();

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_ACCOUNT_SET_PRIVACY_SETTINGS, null,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {

                        try {

                            if (!response.getBoolean("error")) {

                                App.getInstance().setAllowMessagesFromAnyone(response.getInt("allowMessagesFromAnyone"));
                                App.getInstance().setAllowGalleryComments(response.getInt("allowGalleryComments"));
                                App.getInstance().setAllowShowProfileOnlyToFriends(response.getInt("allowShowProfileOnlyToFriends"));
                                App.getInstance().setAllowShowOnline(response.getInt("allowShowOnline"));
                                App.getInstance().setAllowShowPhoneNumber(response.getInt("allowShowPhoneNumber"));

                                checkAllowMessagesFromAnyone(App.getInstance().getAllowMessagesFromAnyone());
                                checkAllowGalleryComments(App.getInstance().getAllowGalleryComments());
                                checkAllowShowProfileOnlyToFriends(App.getInstance().getAllowShowProfileOnlyToFriends());
                                checkAllowShowOnline(App.getInstance().getAllowShowOnline());
                                checkAllowShowPhoneNumber(App.getInstance().getAllowShowPhoneNumber());
                            }

                        } catch (JSONException e) {

                            e.printStackTrace();

                        } finally {

                            loading = false;

                            hidepDialog();
                        }
                    }
                }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

                loading = false;

                hidepDialog();

                Log.e("GCM Settings", error.toString());
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("clientId", CLIENT_ID);
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());
                params.put("allowMessagesFromAnyone", Integer.toString(allowMessagesFromAnyone));
                params.put("allowGalleryComments", Integer.toString(allowGalleryComments));
                params.put("allowShowProfileOnlyToFriends", Integer.toString(allowShowProfileOnlyToFriends));
                params.put("allowShowOnline", Integer.toString(allowShowOnline));
                params.put("allowShowPhoneNumber", Integer.toString(allowShowPhoneNumber));

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }

    protected void initpDialog() {

        pDialog = new ProgressDialog(getActivity());
        pDialog.setMessage(getString(R.string.msg_loading));
        pDialog.setCancelable(false);
    }

    protected void showpDialog() {

        if (!pDialog.isShowing())
            pDialog.show();
    }

    protected void hidepDialog() {

        if (pDialog.isShowing())
            pDialog.dismiss();
    }
}