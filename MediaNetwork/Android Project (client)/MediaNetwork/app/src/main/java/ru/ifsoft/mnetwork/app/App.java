package ru.ifsoft.mnetwork.app;

import android.app.Application;
import android.content.Context;
import android.content.SharedPreferences;
import android.location.Address;
import android.location.Geocoder;
import android.net.ConnectivityManager;
import android.net.NetworkInfo;
import android.text.TextUtils;
import android.util.Log;

import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.RetryPolicy;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.ImageLoader;
import com.android.volley.toolbox.Volley;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;

import ru.ifsoft.mnetwork.R;
import ru.ifsoft.mnetwork.constants.Constants;
import ru.ifsoft.mnetwork.util.CustomRequest;
import ru.ifsoft.mnetwork.util.GPSTracker;
import ru.ifsoft.mnetwork.util.LruBitmapCache;

public class App extends Application implements Constants {

	public static final String TAG = App.class.getSimpleName();

    private Settings settings;

	private RequestQueue mRequestQueue;
	private ImageLoader mImageLoader;

	private static App mInstance;

    private SharedPreferences sharedPref;

    private GPSTracker gps;

    private ArrayList<String> categories;

    private String first_name, surname, username, email, fullname, accessToken, gcmToken = "", fb_id = "", photoUrl = "", coverUrl = "", area = "", country = "", city = "", phone = "";
    private Double lat = 0.000000, lng = 0.000000;
    private long id;
    private int distance = 50, account_access_level, account_type, account_state, email_verified, verified, balance, allowMessagesFromAnyone, allowGalleryComments, allowItemsComments, allowShowProfileOnlyToFriends, allowShowOnline, allowShowPhoneNumber, allowItemsFromFriends, allowFriendsRequestsGCM, allowReviewsGCM, allowCommentsGCM, allowMessagesGCM, allowLikesGCM, allowCommentReplyGCM, errorCode, currentChatId = 0, notificationsCount = 0, messagesCount = 0, friendsCount = 0;

	@Override
	public void onCreate() {

		super.onCreate();

        mInstance = this;

        sharedPref = this.getSharedPreferences(getString(R.string.settings_file), Context.MODE_PRIVATE);

        settings = new Settings();

        this.readData();

        getLocation();
	}

    public void getLocation() {

        if (App.getInstance().isConnected()) {

            gps = new GPSTracker(this);

            if (gps.canGetLocation()) {

                final double latitude = gps.getLatitude();
                final double longitude = gps.getLongitude();

                if (App.getInstance().isConnected() && App.getInstance().getId() != 0) {

                    CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_ACCOUNT_SET_GEO_LOCATION, null,
                            new Response.Listener<JSONObject>() {
                                @Override
                                public void onResponse(JSONObject response) {

                                    try {

                                        if (!response.getBoolean("error")) {

//                                            Toast.makeText(getApplicationContext(), response.toString(), Toast.LENGTH_SHORT).show();
                                        }

                                    } catch (JSONException e) {

                                        e.printStackTrace();
                                    }
                                }
                            }, new Response.ErrorListener() {
                        @Override
                        public void onErrorResponse(VolleyError error) {

                            //Toast.makeText(getApplicationContext(), getString(R.string.msg_network_error), Toast.LENGTH_SHORT).show();
                        }
                    }) {

                        @Override
                        protected Map<String, String> getParams() {
                            Map<String, String> params = new HashMap<String, String>();
                            params.put("accountId", Long.toString(App.getInstance().getId()));
                            params.put("accessToken", App.getInstance().getAccessToken());
                            params.put("lat", Double.toString(latitude));
                            params.put("lng", Double.toString(longitude));

                            return params;
                        }
                    };

                    App.getInstance().addToRequestQueue(jsonReq);
                }

                Geocoder geocoder;
                List<Address> addresses;
                geocoder = new Geocoder(getApplicationContext(), Locale.ENGLISH);

                try {

                    addresses = geocoder.getFromLocation(latitude, longitude, 1);

                    if (addresses.size() > 0) {

                        App.getInstance().setLat(latitude);
                        App.getInstance().setLng(longitude);

                        sharedPref.edit().putString(getString(R.string.settings_account_lat), Double.toString(this.getLat())).apply();
                        sharedPref.edit().putString(getString(R.string.settings_account_lng), Double.toString(this.getLng())).apply();

                        App.getInstance().setArea(addresses.get(0).getAdminArea());
                        App.getInstance().setCity(addresses.get(0).getLocality());
                        App.getInstance().setCountry(addresses.get(0).getCountryName());

//                        Toast.makeText(this, addresses.get(0).getLocality() + ", " + addresses.get(0).getCountryName(), Toast.LENGTH_LONG).show();
                    }

                } catch (IOException e) {

                    // TODO Auto-generated catch block
                    e.printStackTrace();
                }
            }
        }
    }
    
    public boolean isConnected() {
    	
    	ConnectivityManager cm = (ConnectivityManager) getSystemService(Context.CONNECTIVITY_SERVICE);
    	
    	NetworkInfo netInfo = cm.getActiveNetworkInfo();
    	
    	if (netInfo != null && netInfo.isConnectedOrConnecting()) {
    		
    		return true;
    	}
    	
    	return false;
    }

    public void logout() {

        if (App.getInstance().isConnected() && App.getInstance().getId() != 0) {

            CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_ACCOUNT_LOGOUT, null,
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject response) {

                            try {

                                if (!response.getBoolean("error")) {



                                }

                            } catch (JSONException e) {

                                e.printStackTrace();

                            }
                        }
                    }, new Response.ErrorListener() {
                @Override
                public void onErrorResponse(VolleyError error) {

                    App.getInstance().removeData();
                    App.getInstance().readData();
                }
            }) {

                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<String, String>();
                    params.put("clientId", CLIENT_ID);
                    params.put("accountId", Long.toString(App.getInstance().getId()));
                    params.put("accessToken", App.getInstance().getAccessToken());

                    return params;
                }
            };

            App.getInstance().addToRequestQueue(jsonReq);

        }

        App.getInstance().removeData();
        App.getInstance().readData();
    }

    public void get_settings() {

        if (App.getInstance().isConnected()) {

            CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_APP_GET_SETTINGS, null,
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject response) {

                            try {

                                if (!response.getBoolean("error")) {

                                    if (response.has("settings")) {

                                        App.getInstance().getSettings().read_from_json(response.getJSONObject("settings"));
                                    }
                                }

                            } catch (JSONException e) {

                                e.printStackTrace();

                            } finally {

                                Log.e("APP GET SETTINGS", response.toString());
                            }
                        }
                    }, new Response.ErrorListener() {
                @Override
                public void onErrorResponse(VolleyError error) {

                    Log.e("App.get_settings", error.toString());
                }
            }) {

                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<String, String>();
                    params.put("clientId", CLIENT_ID);
                    params.put("accountId", Long.toString(App.getInstance().getId()));
                    params.put("accessToken", App.getInstance().getAccessToken());

                    return params;
                }
            };

            App.getInstance().addToRequestQueue(jsonReq);
        }
    }

    public void read_settings() {

        if (App.getInstance().isConnected() && App.getInstance().getId() != 0) {

            CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_ACCOUNT_GET_SETTINGS, null,
                    new Response.Listener<JSONObject>() {
                        @Override
                        public void onResponse(JSONObject response) {

                            try {

                                if (!response.getBoolean("error")) {

                                    if (response.has("messagesCount")) {

                                        App.getInstance().setMessagesCount(response.getInt("messagesCount"));
                                    }

                                    if (response.has("notificationsCount")) {

                                        App.getInstance().setNotificationsCount(response.getInt("notificationsCount"));
                                    }

                                    if (response.has("friendsCount")) {

                                        App.getInstance().setFriendsCount(response.getInt("friendsCount"));
                                    }

//                                    if (response.has("categories")) {
//
//                                        categories = new ArrayList<String>();
//
//                                        int arrayLength = 0;
//
//                                        JSONObject categoriesObj = (JSONObject) response.getJSONObject("categories");
//
//                                        if (categoriesObj.has("items")) {
//
//                                            JSONArray itemsArray = categoriesObj.getJSONArray("items");
//
//                                            arrayLength = itemsArray.length();
//
//                                            if (arrayLength > 0) {
//
//                                                for (int i = 0; i < itemsArray.length(); i++) {
//
//                                                    JSONObject itemObj = (JSONObject) itemsArray.get(i);
//
//                                                    Category c = new Category(itemObj);
//
//                                                    categories.add(c.getTitle());
//                                                }
//                                            }
//                                        }
//                                    }
                                }

                            } catch (JSONException e) {

                                e.printStackTrace();
                            }
                        }
                    }, new Response.ErrorListener() {
                @Override
                public void onErrorResponse(VolleyError error) {

                    Log.e("App.read_settings", error.toString());
                }
            }) {

                @Override
                protected Map<String, String> getParams() {
                    Map<String, String> params = new HashMap<String, String>();
                    params.put("clientId", CLIENT_ID);
                    params.put("accountId", Long.toString(App.getInstance().getId()));
                    params.put("accessToken", App.getInstance().getAccessToken());

                    return params;
                }
            };

            App.getInstance().addToRequestQueue(jsonReq);
        }
    }

    public void updateGeoLocation() {

        // empty here ;)
    }

    public Boolean authorize(JSONObject authObj) {

        try {

            if (authObj.has("settings")) {

                App.getInstance().getSettings().read_from_json(authObj.getJSONObject("settings"));
            }

            if (authObj.has("error_code")) {

                this.setErrorCode(authObj.getInt("error_code"));
            }

            if (!authObj.has("error")) {

                return false;
            }

            if (authObj.getBoolean("error")) {

                return false;
            }

            if (!authObj.has("account")) {

                return false;
            }

            JSONArray accountArray = authObj.getJSONArray("account");

            if (accountArray.length() > 0) {

                JSONObject accountObj = (JSONObject) accountArray.get(0);

                this.setPhone(accountObj.getString("phone"));

                this.setFirstname(accountObj.getString("first_name"));
                this.setSurname(accountObj.getString("surname"));
                this.setUsername(accountObj.getString("username"));
                this.setFullname(accountObj.getString("fullname"));
                this.setEmail(accountObj.getString("email"));
                this.setEmailVerified(accountObj.getInt("email_verified"));
                this.setState(accountObj.getInt("account_state"));
                this.setType(accountObj.getInt("account_type"));
                this.setAccessLevel(accountObj.getInt("account_access_level"));
                this.setVerified(accountObj.getInt("verified"));
                this.setBalance(accountObj.getInt("balance"));
                this.setFacebookId(accountObj.getString("fb_id"));
                this.setAllowReviewsGCM(accountObj.getInt("allowReviewsGCM"));
                this.setAllowMessagesFromAnyone(accountObj.getInt("allowMessagesFromAnyone"));
                this.setAllowGalleryComments(accountObj.getInt("allowGalleryComments"));
                this.setAllowItemsComments(accountObj.getInt("allowItemsComments"));
                this.setAllowShowOnline(accountObj.getInt("allowShowOnline"));
                this.setAllowShowPhoneNumber(accountObj.getInt("allowShowPhoneNumber"));
                this.setAllowShowProfileOnlyToFriends(accountObj.getInt("allowShowProfileOnlyToFriends"));
                this.setAllowItemsFromFriends(accountObj.getInt("allowItemsFromFriends"));
                this.setAllowFriendsRequestsGCM(accountObj.getInt("allowFriendsRequestsGCM"));
                this.setAllowCommentsGCM(accountObj.getInt("allowCommentsGCM"));
                this.setAllowMessagesGCM(accountObj.getInt("allowMessagesGCM"));
                this.setAllowLikesGCM(accountObj.getInt("allowLikesGCM"));
                this.setAllowCommentReplyGCM(accountObj.getInt("allowCommentReplyGCM"));

                this.setPhotoUrl(accountObj.getString("photoUrl"));
                this.setCoverUrl(accountObj.getString("coverUrl"));

                this.setNotificationsCount(accountObj.getInt("notificationsCount"));
                this.setMessagesCount(accountObj.getInt("messagesCount"));
                this.setFriendsCount(accountObj.getInt("friendsCount"));

                if (App.getInstance().getLat() == 0.000000 && App.getInstance().getLng() == 0.000000) {

                    this.setLat(accountObj.getDouble("lat"));
                    this.setLng(accountObj.getDouble("lng"));
                }
            }

            this.setId(authObj.getLong("accountId"));
            this.setAccessToken(authObj.getString("accessToken"));

            this.saveData();

            this.read_settings();

            if (getGcmToken().length() != 0) {

                setGcmToken(getGcmToken());
            }

            return true;

        } catch (JSONException e) {

            e.printStackTrace();
            return false;
        }
    }

    public void setDistance(int distance) {

        this.distance = distance;
    }

    public int getDistance() {

        return this.distance;
    }

    public long getId() {

        return this.id;
    }

    public void setId(long id) {

        this.id = id;
    }

    public void setGcmToken(final String gcmToken) {

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_ACCOUNT_SET_GCM_TOKEN, null,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {

                        try {

                            if (!response.getBoolean("error")) {

                                Log.e("", "Success save FCM token on server");
                            }

                        } catch (JSONException e) {

                            e.printStackTrace();
                        }
                    }
                }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

                Log.e("", "Error save FCM token on server");
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());

                params.put("gcm_regId", gcmToken);

                return params;
            }
        };

        int socketTimeout = 0;//0 seconds - change to what you want
        RetryPolicy policy = new DefaultRetryPolicy(socketTimeout, 0, DefaultRetryPolicy.DEFAULT_BACKOFF_MULT);

        jsonReq.setRetryPolicy(policy);

        App.getInstance().addToRequestQueue(jsonReq);

        this.gcmToken = gcmToken;
    }

    public String getGcmToken() {

        return this.gcmToken;
    }

    public void setFacebookId(String fb_id) {

        this.fb_id = fb_id;
    }

    public String getFacebookId() {

        return this.fb_id;
    }

    public void setState(int account_state) {

        this.account_state = account_state;
    }

    public int getState() {

        return this.account_state;
    }

    public void setType(int account_type) {

        this.account_type = account_type;
    }

    public int getType() {

        return this.account_type;
    }

    public void setAccessLevel(int account_access_level) {

        this.account_access_level = account_access_level;
    }

    public int getAccessLevel() {

        return this.account_access_level;
    }

    public void setNotificationsCount(int notificationsCount) {

        this.notificationsCount = notificationsCount;
    }

    public int getNotificationsCount() {

        return this.notificationsCount;
    }

    public void setMessagesCount(int messagesCount) {

        this.messagesCount = messagesCount;
    }

    public int getMessagesCount() {

        return this.messagesCount;
    }

    public void setFriendsCount(int friendsCount) {

        this.friendsCount = friendsCount;
    }

    public int getFriendsCount() {

        return this.friendsCount;
    }

    public void setAllowMessagesGCM(int allowMessagesGCM) {

        this.allowMessagesGCM = allowMessagesGCM;
    }

    public int getAllowMessagesGCM() {

        return this.allowMessagesGCM;
    }

    public void setAllowLikesGCM(int allowLikesGCM) {

        this.allowLikesGCM = allowLikesGCM;
    }

    public int getAllowLikesGCM() {

        return this.allowLikesGCM;
    }

    public void setAllowFriendsRequestsGCM(int allowFriendsRequestsGCM) {

        this.allowFriendsRequestsGCM = allowFriendsRequestsGCM;
    }

    public int getAllowFriendsRequestsGCM() {

        return this.allowFriendsRequestsGCM;
    }

    public void setAllowCommentReplyGCM(int allowCommentReplyGCM) {

        this.allowCommentReplyGCM = allowCommentReplyGCM;
    }

    public int getAllowCommentReplyGCM() {

        return this.allowCommentReplyGCM;
    }

    public void setAllowCommentsGCM(int allowCommentsGCM) {

        this.allowCommentsGCM = allowCommentsGCM;
    }

    public int getAllowCommentsGCM() {

        return this.allowCommentsGCM;
    }

    public void setAllowReviewsGCM(int allowReviewsGCM) {

        this.allowReviewsGCM = allowReviewsGCM;
    }

    public int getAllowReviewsGCM() {

        return this.allowReviewsGCM;
    }

    public void setAllowMessagesFromAnyone(int allowMessagesFromAnyone) {

        this.allowMessagesFromAnyone = allowMessagesFromAnyone;
    }

    public int getAllowMessagesFromAnyone() {

        return this.allowMessagesFromAnyone;
    }

    public void setAllowGalleryComments(int allowGalleryComments) {

        this.allowGalleryComments = allowGalleryComments;
    }

    public int getAllowGalleryComments() {

        return this.allowGalleryComments;
    }

    public void setAllowItemsComments(int allowItemsComments) {

        this.allowItemsComments = allowItemsComments;
    }

    public int getAllowItemsComments() {

        return this.allowItemsComments;
    }

    public void setAllowShowOnline(int allowShowOnline) {

        this.allowShowOnline = allowShowOnline;
    }

    public int getAllowShowOnline() {

        return this.allowShowOnline;
    }

    public void setAllowShowPhoneNumber(int allowShowPhoneNumber) {

        this.allowShowPhoneNumber = allowShowPhoneNumber;
    }

    public int getAllowShowPhoneNumber() {

        return this.allowShowPhoneNumber;
    }

    public void setAllowShowProfileOnlyToFriends(int allowShowProfileOnlyToFriends) {

        this.allowShowProfileOnlyToFriends = allowShowProfileOnlyToFriends;
    }

    public int getAllowShowProfileOnlyToFriends() {

        return this.allowShowProfileOnlyToFriends;
    }

    public void setAllowItemsFromFriends(int allowItemsFromFriends) {

        this.allowItemsFromFriends = allowItemsFromFriends;
    }

    public int getAllowItemsFromFriends() {

        return this.allowItemsFromFriends;
    }

    public void setCurrentChatId(int currentChatId) {

        this.currentChatId = currentChatId;
    }

    public int getCurrentChatId() {

        return this.currentChatId;
    }

    public void setErrorCode(int errorCode) {

        this.errorCode = errorCode;
    }

    public int getErrorCode() {

        return this.errorCode;
    }

    public String getUsername() {

        if (this.username == null) {

            this.username = "";
        }

        return this.username;
    }

    public void setUsername(String username) {

        this.username = username;
    }

    public void setFirstname(String first_name) {

        this.first_name = first_name;
    }

    public String getFirstname() {

        if (this.first_name == null) {

            this.first_name = "";
        }

        return this.first_name;
    }

    public void setSurname(String surname) {

        this.surname = surname;
    }

    public String getSurname() {

        if (this.surname == null) {

            this.surname = "";
        }

        return this.surname;
    }

    public void setEmail(String email) {

        this.email = email;
    }

    public String getEmail() {

        if (this.email == null) {

            this.email = "";
        }

        return this.email;
    }

    public String getPhone() {

        if (this.phone == null) {

            this.phone = "";
        }

        return this.phone;
    }

    public void setPhone(String phone) {

        this.phone = phone;
    }

    public String getAccessToken() {

        return this.accessToken;
    }

    public void setAccessToken(String accessToken) {

        this.accessToken = accessToken;
    }

    public void setFullname(String fullname) {

        this.fullname = fullname;
    }

    public String getFullname() {

        if (this.fullname == null) {

            this.fullname = "";
        }

        return this.fullname;
    }

    public void setVerified(int verified) {

        this.verified = verified;
    }

    public int getVerified() {

        return this.verified;
    }

    public Boolean isVerified() {

        if (this.verified == 1) {

            return true;

        } else {

            return false;
        }
    }

    public void setEmailVerified(int email_verified) {

        this.email_verified = email_verified;
    }

    public int getEmailVerified() {

        return this.email_verified;
    }

    public Boolean isEmailVerified() {

        if (this.email_verified == 1) {

            return true;

        } else {

            return false;
        }
    }

    public void setBalance(int balance) {

        this.balance = balance;
    }

    public int getBalance() {

        return this.balance;
    }

    public void setSettings(Settings settings) {

        this.settings = settings;
    }

    public Settings getSettings() {

        return this.settings;
    }

    public void setPhotoUrl(String photoUrl) {

        this.photoUrl = photoUrl;
    }

    public String getPhotoUrl() {

        if (this.photoUrl == null) {

            this.photoUrl = "";
        }

        return this.photoUrl;
    }

    public void setCoverUrl(String coverUrl) {

        this.coverUrl = coverUrl;
    }

    public String getCoverUrl() {

        if (coverUrl == null) {

            this.coverUrl = "";
        }

        return this.coverUrl;
    }

    public void setCountry(String country) {

        this.country = country;
    }

    public String getCountry() {

        if (this.country == null) {

            this.setCountry("");
        }

        return this.country;
    }

    public void setCity(String city) {

        this.city = city;
    }

    public String getCity() {

        if (this.city == null) {

            this.setCity("");
        }

        return this.city;
    }

    public void setArea(String area) {

        this.area = area;
    }

    public String getArea() {

        if (this.area == null) {

            this.setArea("");
        }

        return this.area;
    }

    public void setLat(Double lat) {

        if (this.lat == null) {

            this.lat = 0.000000;
        }

        this.lat = lat;
    }

    public Double getLat() {

        if (this.lat == null) {

            this.lat = 0.000000;
        }

        return this.lat;
    }

    public void setLng(Double lng) {

        if (this.lng == null) {

            this.lng = 0.000000;
        }

        this.lng = lng;
    }

    public Double getLng() {

        return this.lng;
    }

    public ArrayList<String> getCategoriesList() {

        if (this.categories == null) {

            this.categories = new ArrayList<String>();
        }

        return this.categories;
    }

    public void readData() {

        this.setId(sharedPref.getLong(getString(R.string.settings_account_id), 0));
        this.setUsername(sharedPref.getString(getString(R.string.settings_account_username), ""));
        this.setAccessToken(sharedPref.getString(getString(R.string.settings_account_access_token), ""));

        this.setAllowMessagesGCM(sharedPref.getInt(getString(R.string.settings_account_allow_messages_gcm), 1));
        this.setAllowCommentsGCM(sharedPref.getInt(getString(R.string.settings_account_allow_comments_gcm), 1));
        this.setAllowCommentReplyGCM(sharedPref.getInt(getString(R.string.settings_account_allow_comments_reply_gcm), 1));

        this.setMessagesCount(sharedPref.getInt(getString(R.string.settings_account_messages_count), 0));
        this.setNotificationsCount(sharedPref.getInt(getString(R.string.settings_account_notifications_count), 0));

        this.setFullname(sharedPref.getString(getString(R.string.settings_account_fullname), ""));
        this.setPhotoUrl(sharedPref.getString(getString(R.string.settings_account_photo_url), ""));
        this.setCoverUrl(sharedPref.getString(getString(R.string.settings_account_cover_url), ""));

        this.setLat(Double.parseDouble(sharedPref.getString(getString(R.string.settings_account_lat), "0.000000")));
        this.setLng(Double.parseDouble(sharedPref.getString(getString(R.string.settings_account_lng), "0.000000")));

        this.settings.setNavMessagesMenuItem(sharedPref.getInt(getString(R.string.settings_nav_messages_menu_item), 1));
        this.settings.setNavNotificationsMenuItem(sharedPref.getInt(getString(R.string.settings_nav_notifications_menu_item), 1));

        this.settings.setAllowFacebookAuthorization(sharedPref.getInt(getString(R.string.settings_allow_facebook_authorization), 1));
        this.settings.setAllowLogin(sharedPref.getInt(getString(R.string.settings_allow_login), 1));
        this.settings.setAllowSignUp(sharedPref.getInt(getString(R.string.settings_allow_signup), 1));
        this.settings.setAllowPasswordRecovery(sharedPref.getInt(getString(R.string.settings_allow_password_recovery), 1));
        this.settings.setAllowAdmobBanner(sharedPref.getInt(getString(R.string.settings_allow_admob), 1));
        this.settings.setAllowAddVideoToGallery(sharedPref.getInt(getString(R.string.settings_allow_add_video_to_gallery), 1));
        this.settings.setAllowEmoji(sharedPref.getInt(getString(R.string.settings_allow_emoji), 1));
        this.settings.setAllowAddImageToMessage(sharedPref.getInt(getString(R.string.settings_allow_add_image_to_message), 1));
        this.settings.setAllowSeenFunction(sharedPref.getInt(getString(R.string.settings_allow_seen_function), 1));
        this.settings.setAllowTypingFunction(sharedPref.getInt(getString(R.string.settings_allow_typing_function), 1));
    }

    public void saveData() {

        sharedPref.edit().putLong(getString(R.string.settings_account_id), this.getId()).apply();
        sharedPref.edit().putString(getString(R.string.settings_account_username), this.getUsername()).apply();
        sharedPref.edit().putString(getString(R.string.settings_account_access_token), this.getAccessToken()).apply();

        sharedPref.edit().putInt(getString(R.string.settings_account_allow_messages_gcm), this.getAllowMessagesGCM()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_account_allow_comments_gcm), this.getAllowCommentsGCM()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_account_allow_comments_reply_gcm), this.getAllowCommentReplyGCM()).apply();

        sharedPref.edit().putInt(getString(R.string.settings_account_messages_count), this.getMessagesCount()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_account_notifications_count), this.getNotificationsCount()).apply();

        sharedPref.edit().putString(getString(R.string.settings_account_fullname), this.getFullname()).apply();
        sharedPref.edit().putString(getString(R.string.settings_account_photo_url), this.getPhotoUrl()).apply();
        sharedPref.edit().putString(getString(R.string.settings_account_cover_url), this.getCoverUrl()).apply();

        sharedPref.edit().putString(getString(R.string.settings_account_lat), Double.toString(this.getLat())).apply();
        sharedPref.edit().putString(getString(R.string.settings_account_lng), Double.toString(this.getLng())).apply();

        sharedPref.edit().putInt(getString(R.string.settings_nav_messages_menu_item), this.settings.getNavMessagesMenuItem()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_nav_notifications_menu_item), this.settings.getNavNotificationsMenuItem()).apply();

        sharedPref.edit().putInt(getString(R.string.settings_allow_facebook_authorization), this.settings.getAllowFacebookAuthorization()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_login), this.settings.getAllowLogIn()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_signup), this.settings.getAllowSignUp()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_password_recovery), this.settings.getAllowPasswordRecovery()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_admob), this.settings.getAllowAdmobBanner()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_add_video_to_gallery), this.settings.getAllowAddVideoToGallery()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_emoji), this.settings.getAllowEmoji()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_add_image_to_message), this.settings.getAllowAddImageToMessage()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_seen_function), this.settings.getAllowSeenFunction()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_typing_function), this.settings.getAllowTypingFunction()).apply();
    }

    public void removeData() {

        sharedPref.edit().putLong(getString(R.string.settings_account_id), 0).apply();
        sharedPref.edit().putString(getString(R.string.settings_account_username), "").apply();
        sharedPref.edit().putString(getString(R.string.settings_account_access_token), "").apply();

        sharedPref.edit().putInt(getString(R.string.settings_account_messages_count), 0).apply();
        sharedPref.edit().putInt(getString(R.string.settings_account_notifications_count), 0).apply();

        sharedPref.edit().putString(getString(R.string.settings_account_lat), Double.toString(0.000000)).apply();
        sharedPref.edit().putString(getString(R.string.settings_account_lng), Double.toString(0.000000)).apply();
    }

    public static synchronized App getInstance() {
		return mInstance;
	}

	public RequestQueue getRequestQueue() {

		if (mRequestQueue == null) {

			mRequestQueue = Volley.newRequestQueue(getApplicationContext());
		}

		return mRequestQueue;
	}

	public ImageLoader getImageLoader() {

		getRequestQueue();

		if (mImageLoader == null) {

			mImageLoader = new ImageLoader(this.mRequestQueue, new LruBitmapCache());
		}

		return this.mImageLoader;
	}

	public <T> void addToRequestQueue(Request<T> req, String tag) {

		// set the default tag if tag is empty
		req.setTag(TextUtils.isEmpty(tag) ? TAG : tag);
		getRequestQueue().add(req);
	}

	public <T> void addToRequestQueue(Request<T> req) {

		req.setTag(TAG);
		getRequestQueue().add(req);
	}

	public void cancelPendingRequests(Object tag) {

		if (mRequestQueue != null) {

			mRequestQueue.cancelAll(tag);
		}
	}
}