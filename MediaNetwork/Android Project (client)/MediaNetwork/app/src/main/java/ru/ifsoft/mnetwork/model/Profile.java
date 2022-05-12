package ru.ifsoft.mnetwork.model;

import android.app.Application;
import android.os.Parcel;
import android.os.Parcelable;
import android.util.Log;

import org.json.JSONObject;

import ru.ifsoft.mnetwork.constants.Constants;


public class Profile extends Application implements Constants, Parcelable {

    private long id;

    private int state, sex, sex_orientation, year, month, day, verified, itemsCount, likesCount, friendsCount, galleryItemsCount, commentsCount, reviewsCount, lastAuthorize, allowMessagesFromAnyone, allowItemsFromFriends, allowShowOnline, allowShowProfileOnlyToFriends, allowShowPhoneNumber;

    private double distance = 0;

    private String phone, username, fullname, photoUrl, coverUrl, location, facebookPage, instagramPage, bio, lastAuthorizeDate, lastAuthorizeTimeAgo;

    private Boolean blocked = false;

    private Boolean inBlackList = false;

    private Boolean online = false;

    private Boolean friend = false;

    private Boolean friend_request = false;

    private String gcm_regId = "";

    public Profile() {


    }

    public Profile(JSONObject jsonData) {

        try {

            if (!jsonData.getBoolean("error")) {

                this.setAllowMessagesFromAnyone(jsonData.getInt("allowMessagesFromAnyone"));
                this.setAllowItemsFromFriends(jsonData.getInt("allowItemsFromFriends"));
                this.setAllowShowOnline(jsonData.getInt("allowShowOnline"));
                this.setAllowShowProfileOnlyToFriends(jsonData.getInt("allowShowProfileOnlyToFriends"));
                this.setAllowShowPhoneNumber(jsonData.getInt("allowShowPhoneNumber"));

                this.setId(jsonData.getLong("id"));
                this.setState(jsonData.getInt("account_state"));
                this.setSex(jsonData.getInt("sex"));
                this.setSexOrientation(jsonData.getInt("sex_orientation"));
                this.setYear(jsonData.getInt("year"));
                this.setMonth(jsonData.getInt("month"));
                this.setDay(jsonData.getInt("day"));
                this.setUsername(jsonData.getString("username"));
                this.setFullname(jsonData.getString("fullname"));
                this.setLocation(jsonData.getString("location"));
                this.setFacebookPage(jsonData.getString("fb_page"));
                this.setInstagramPage(jsonData.getString("instagram_page"));
                this.setBio(jsonData.getString("status"));
                this.setVerified(jsonData.getInt("verified"));

                this.setPhotoUrl(jsonData.getString("photoUrl"));
                this.setCoverUrl(jsonData.getString("coverUrl"));

                this.setItemsCount(jsonData.getInt("itemsCount"));
                this.setReviewsCount(jsonData.getInt("reviewsCount"));
                this.setCommentsCount(jsonData.getInt("commentsCount"));
                this.setLikesCount(jsonData.getInt("likesCount"));
                this.setGalleryItemsCount(jsonData.getInt("galleryItemsCount"));
                this.setFriendsCount(jsonData.getInt("friendsCount"));

                this.setPhone(jsonData.getString("phone"));

                this.setOnline(jsonData.getBoolean("online"));
                this.setFriend(jsonData.getBoolean("friend"));
                this.setFriendRequest(jsonData.getBoolean("friend_request"));

                this.setLastActive(jsonData.getInt("lastAuthorize"));
                this.setLastActiveDate(jsonData.getString("lastAuthorizeDate"));
                this.setLastActiveTimeAgo(jsonData.getString("lastAuthorizeTimeAgo"));

                this.setInBlackList(jsonData.getBoolean("inBlackList"));
                this.setBlocked(jsonData.getBoolean("blocked"));

                if (jsonData.has("distance")) {

                    this.setDistance(jsonData.getDouble("distance"));
                }

                if (jsonData.has("gcm_regid")) {

                    this.setGcmRegId(jsonData.getString("gcm_regid"));
                }
            }

        } catch (Throwable t) {

            Log.e("Profile", "Could not parse malformed JSON: \"" + jsonData.toString() + "\"");

        } finally {

            Log.d("Profile", jsonData.toString());
        }
    }

    public void setDistance(double distance) {

        this.distance = distance;
    }

    public double getDistance() {

        return this.distance;
    }

    public void setId(long profile_id) {

        this.id = profile_id;
    }

    public long getId() {

        return this.id;
    }

    public void setState(int profileState) {

        this.state = profileState;
    }

    public int getState() {

        return this.state;
    }

    public void setSex(int sex) {

        this.sex = sex;
    }

    public int getSex() {

        return this.sex;
    }

    public void setYear(int year) {

        this.year = year;
    }

    public int getYear() {

        return this.year;
    }

    public void setMonth(int month) {

        this.month = month;
    }

    public int getMonth() {

        return this.month;
    }

    public void setDay(int day) {

        this.day = day;
    }

    public int getDay() {

        return this.day;
    }

    public void setSexOrientation(int sex_orientation) {

        this.sex_orientation = sex_orientation;
    }

    public int getSexOrientation() {

        return this.sex_orientation;
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
        }

        return false;
    }

    public void setUsername(String profile_username) {

        this.username = profile_username;
    }

    public String getUsername() {

        return this.username;
    }

    public void setFullname(String profile_fullname) {

        this.fullname = profile_fullname;
    }

    public String getFullname() {

        return this.fullname;
    }

    public void setLocation(String location) {

        this.location = location;
    }

    public String getLocation() {

        if (this.location == null) {

            this.location = "";
        }

        return this.location;
    }

    public void setFacebookPage(String facebookPage) {

        this.facebookPage = facebookPage;
    }

    public String getFacebookPage() {

        return this.facebookPage;
    }

    public void setInstagramPage(String instagramPage) {

        this.instagramPage = instagramPage;
    }

    public String getInstagramPage() {

        return this.instagramPage;
    }

    public void setBio(String bio) {

        this.bio = bio;
    }

    public String getBio() {

        return this.bio;
    }

    public void setPhotoUrl(String photoUrl) {

        this.photoUrl = photoUrl;
    }

    public String getPhotoUrl() {

        return this.photoUrl;
    }

    public void setCoverUrl(String coverUrl) {

        this.coverUrl = coverUrl;
    }

    public String getCoverUrl() {

        return this.coverUrl;
    }

    public void setItemsCount(int itemsCount) {

        this.itemsCount = itemsCount;
    }

    public int getItemsCount() {

        return this.itemsCount;
    }

    public void setCommentsCount(int commentsCount) {

        this.commentsCount = commentsCount;
    }

    public int getCommentsCount() {

        return this.commentsCount;
    }

    public void setLikesCount(int likesCount) {

        this.likesCount = likesCount;
    }

    public int getLikesCount() {

        return this.likesCount;
    }

    public void setFriendsCount(int friendsCount) {

        this.friendsCount = friendsCount;
    }

    public int getFriendsCount() {

        return this.friendsCount;
    }

    public void setGalleryItemsCount(int galleryItemsCount) {

        this.galleryItemsCount = galleryItemsCount;
    }

    public int getGalleryItemsCount() {

        return this.galleryItemsCount;
    }

    public void setReviewsCount(int reviewsCount) {

        this.reviewsCount = reviewsCount;
    }

    public int getReviewsCount() {

        return this.reviewsCount;
    }

    public void setPhone(String phone) {

        this.phone = phone;
    }

    public String getPhone() {

        return this.phone;
    }

    public void setLastActive(int lastAuthorize) {

        this.lastAuthorize = lastAuthorize;
    }

    public int getLastActive() {

        return this.lastAuthorize;
    }

    public void setLastActiveDate(String lastAuthorizeDate) {

        this.lastAuthorizeDate = lastAuthorizeDate;
    }

    public String getLastActiveDate() {

        return this.lastAuthorizeDate;
    }

    public void setLastActiveTimeAgo(String lastAuthorizeTimeAgo) {

        this.lastAuthorizeTimeAgo = lastAuthorizeTimeAgo;
    }

    public String getLastActiveTimeAgo() {

        return this.lastAuthorizeTimeAgo;
    }

    public void setOnline(Boolean online) {

        this.online = online;
    }

    public Boolean isOnline() {

        return this.online;
    }

    public void setFriend(Boolean friend) {

        this.friend = friend;
    }

    public Boolean isFriend() {

        return this.friend;
    }

    public void setFriendRequest(Boolean friend_request) {

        this.friend_request = friend_request;
    }

    public Boolean isFriendRequest() {

        return this.friend_request;
    }

    public void setInBlackList(Boolean inBlackList) {

        this.inBlackList = inBlackList;
    }

    public Boolean isInBlackList() {

        return this.inBlackList;
    }

    public void setBlocked(Boolean blocked) {

        this.blocked = blocked;
    }

    public Boolean isBlocked() {

        return this.blocked;
    }

    public void setAllowMessagesFromAnyone(int allowMessagesFromAnyone) {

        this.allowMessagesFromAnyone = allowMessagesFromAnyone;
    }

    public int getAllowMessagesFromAnyone() {

        return this.allowMessagesFromAnyone;
    }

    public void setAllowItemsFromFriends(int allowItemsFromFriends) {

        this.allowItemsFromFriends = allowItemsFromFriends;
    }

    public int getAllowItemsFromFriends() {

        return this.allowItemsFromFriends;
    }

    public void setAllowShowOnline(int allowShowOnline) {

        this.allowShowOnline = allowShowOnline;
    }

    public int getAllowShowOnline() {

        return this.allowShowOnline;
    }

    public void setAllowShowProfileOnlyToFriends(int allowShowProfileOnlyToFriends) {

        this.allowShowProfileOnlyToFriends = allowShowProfileOnlyToFriends;
    }

    public int getAllowShowProfileOnlyToFriends() {

        return this.allowShowProfileOnlyToFriends;
    }


    public void setAllowShowPhoneNumber(int allowShowPhoneNumber) {

        this.allowShowPhoneNumber = allowShowPhoneNumber;
    }

    public int getAllowShowPhoneNumber() {

        return this.allowShowPhoneNumber;
    }

    public void setGcmRegId(String gcm_regId) {

        this.gcm_regId = gcm_regId;
    }

    public String getGcmRegId() {

        return this.gcm_regId;
    }

    @Override
    public int describeContents() {

        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {

    }
    public static final Parcelable.Creator CREATOR = new Parcelable.Creator() {

        public Profile createFromParcel(Parcel in) {

            return new Profile();
        }

        public Profile[] newArray(int size) {
            return new Profile[size];
        }
    };
}
