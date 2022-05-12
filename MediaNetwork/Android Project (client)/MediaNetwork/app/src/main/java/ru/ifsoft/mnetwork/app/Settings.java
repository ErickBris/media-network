package ru.ifsoft.mnetwork.app;

import android.app.Application;
import android.content.Context;
import android.content.SharedPreferences;
import android.util.Log;

import org.json.JSONObject;

import ru.ifsoft.mnetwork.R;
import ru.ifsoft.mnetwork.constants.Constants;

public class Settings extends Application implements Constants {

	public static final String TAG = Settings.class.getSimpleName();

    private SharedPreferences sharedPref;

    private int allowTypingFunction = 1, allowSeenFunction = 1, allowAddImageToMessage = 1, allowEmoji = 1, allowAdmobBanner = 1, allowAddVideoToGallery = 1, navMessagesMenuItem = 1, navNotificationsMenuItem = 1, allowFacebookAuthorization = 1, allowLogIn = 1, allowSignUp = 1, allowPasswordRecovery = 1;

	@Override
	public void onCreate() {

		super.onCreate();

        sharedPref = App.getInstance().getSharedPreferences(getString(R.string.settings_file), Context.MODE_PRIVATE);
	}

    public void read_from_json(JSONObject jsonData) {

        try {

            if (jsonData.has("navMessagesMenuItem")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("navMessagesMenuItem");

                this.setNavMessagesMenuItem(obj.getInt("intValue"));
            }

            if (jsonData.has("navNotificationsMenuItem")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("navNotificationsMenuItem");

                this.setNavNotificationsMenuItem(obj.getInt("intValue"));
            }

            if (jsonData.has("allowFacebookAuthorization")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowFacebookAuthorization");

                this.setAllowFacebookAuthorization(obj.getInt("intValue"));
            }

            if (jsonData.has("allowLogIn")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowLogIn");

                this.setAllowLogin(obj.getInt("intValue"));
            }

            if (jsonData.has("allowSignUp")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowSignUp");

                this.setAllowSignUp(obj.getInt("intValue"));
            }

            if (jsonData.has("allowAdmobBanner")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowAdmobBanner");

                this.setAllowAdmobBanner(obj.getInt("intValue"));
            }

            if (jsonData.has("allowAddVideoToGallery")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowAddVideoToGallery");

                this.setAllowAddVideoToGallery(obj.getInt("intValue"));
            }

            if (jsonData.has("allowEmoji")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowEmoji");

                this.setAllowEmoji(obj.getInt("intValue"));
            }

            if (jsonData.has("allowPasswordRecovery")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowPasswordRecovery");

                this.setAllowPasswordRecovery(obj.getInt("intValue"));
            }

            if (jsonData.has("allowAddImageToMessage")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowAddImageToMessage");

                this.setAllowAddImageToMessage(obj.getInt("intValue"));
            }

            if (jsonData.has("allowSeenFunction")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowSeenFunction");

                this.setAllowSeenFunction(obj.getInt("intValue"));
            }

            if (jsonData.has("allowTypingFunction")) {

                JSONObject obj = (JSONObject) jsonData.getJSONObject("allowTypingFunction");

                this.setAllowTypingFunction(obj.getInt("intValue"));
            }

        } catch (Throwable t) {

            Log.e("Settings", "Could not parse malformed JSON: \"" + jsonData.toString() + "\"");

        } finally {

//            this.save_settings();
        }
    }

    public void read_settings() {

        this.setNavMessagesMenuItem(sharedPref.getInt(getString(R.string.settings_nav_messages_menu_item), 1));
        this.setNavNotificationsMenuItem(sharedPref.getInt(getString(R.string.settings_nav_notifications_menu_item), 1));

        this.setAllowFacebookAuthorization(sharedPref.getInt(getString(R.string.settings_allow_facebook_authorization), 1));
        this.setAllowLogin(sharedPref.getInt(getString(R.string.settings_allow_login), 1));
        this.setAllowSignUp(sharedPref.getInt(getString(R.string.settings_allow_signup), 1));
        this.setAllowAdmobBanner(sharedPref.getInt(getString(R.string.settings_allow_admob), 1));
        this.setAllowPasswordRecovery(sharedPref.getInt(getString(R.string.settings_allow_password_recovery), 1));
        this.setAllowAddVideoToGallery(sharedPref.getInt(getString(R.string.settings_allow_add_video_to_gallery), 1));
        this.setAllowEmoji(sharedPref.getInt(getString(R.string.settings_allow_emoji), 1));
        this.setAllowAddImageToMessage(sharedPref.getInt(getString(R.string.settings_allow_add_image_to_message), 1));

        this.setAllowSeenFunction(sharedPref.getInt(getString(R.string.settings_allow_seen_function), 1));
        this.setAllowTypingFunction(sharedPref.getInt(getString(R.string.settings_allow_typing_function), 1));

        Log.e("SETTINGS READ", "qq");
    }

    public void save_settings() {

        sharedPref = getSharedPreferences(getString(R.string.settings_file), Context.MODE_PRIVATE);

        sharedPref.edit().putInt(getString(R.string.settings_nav_messages_menu_item), this.getNavMessagesMenuItem()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_nav_notifications_menu_item), this.getNavNotificationsMenuItem()).apply();

        sharedPref.edit().putInt(getString(R.string.settings_allow_facebook_authorization), this.getAllowFacebookAuthorization()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_login), this.getAllowLogIn()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_signup), this.getAllowSignUp()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_admob), this.getAllowAdmobBanner()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_password_recovery), this.getAllowPasswordRecovery()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_add_video_to_gallery), this.getAllowAddVideoToGallery()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_emoji), this.getAllowEmoji()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_add_image_to_message), this.getAllowAddImageToMessage()).apply();

        sharedPref.edit().putInt(getString(R.string.settings_allow_seen_function), this.getAllowSeenFunction()).apply();
        sharedPref.edit().putInt(getString(R.string.settings_allow_typing_function), this.getAllowTypingFunction()).apply();

        Log.e("SETTINGS SAVE", "qq");
    }

    public void setNavNotificationsMenuItem(int navNotificationsMenuItem) {

        this.navNotificationsMenuItem = navNotificationsMenuItem;
    }

    public int getNavNotificationsMenuItem() {

        return this.navNotificationsMenuItem;
    }

    public void setNavMessagesMenuItem(int navMessagesMenuItem) {

        this.navMessagesMenuItem = navMessagesMenuItem;
    }

    public int getAllowFacebookAuthorization() {

        return this.allowFacebookAuthorization;
    }

    public void setAllowFacebookAuthorization(int allowFacebookAuthorization) {

        this.allowFacebookAuthorization = allowFacebookAuthorization;
    }

    public int getAllowLogIn() {

        return this.allowLogIn;
    }

    public void setAllowLogin(int allowLogIn) {

        this.allowLogIn = allowLogIn;
    }

    public int getAllowSignUp() {

        return this.allowSignUp;
    }

    public void setAllowSignUp(int allowSignUp) {

        this.allowSignUp = allowSignUp;
    }

    public int getAllowAdmobBanner() {

        return this.allowAdmobBanner;
    }

    public void setAllowAdmobBanner(int allowAdmobBanner) {

        this.allowAdmobBanner = allowAdmobBanner;
    }

    public int getAllowAddVideoToGallery() {

        return this.allowAddVideoToGallery;
    }

    public void setAllowAddVideoToGallery(int allowAddVideoToGallery) {

        this.allowAddVideoToGallery = allowAddVideoToGallery;
    }

    public int getAllowPasswordRecovery() {

        return this.allowPasswordRecovery;
    }

    public void setAllowPasswordRecovery(int allowPasswordRecovery) {

        this.allowPasswordRecovery = allowPasswordRecovery;
    }

    public int getAllowEmoji() {

        return this.allowEmoji;
    }

    public void setAllowEmoji(int allowEmoji) {

        this.allowEmoji = allowEmoji;
    }

    public int getNavMessagesMenuItem() {

        return this.navMessagesMenuItem;
    }

    public int getAllowAddImageToMessage() {

        return this.allowAddImageToMessage;
    }

    public void setAllowAddImageToMessage(int allowAddImageToMessage) {

        this.allowAddImageToMessage = allowAddImageToMessage;
    }

    public int getAllowSeenFunction() {

        return this.allowSeenFunction;
    }

    public void setAllowSeenFunction(int allowSeenFunction) {

        this.allowSeenFunction = allowSeenFunction;
    }

    public int getAllowTypingFunction() {

        return this.allowTypingFunction;
    }

    public void setAllowTypingFunction(int allowTypingFunction) {

        this.allowTypingFunction = allowTypingFunction;
    }
}