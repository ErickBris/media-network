package ru.ifsoft.mnetwork.model;

import android.app.Application;
import android.os.Parcel;
import android.os.Parcelable;
import android.util.Log;

import org.json.JSONObject;

import ru.ifsoft.mnetwork.constants.Constants;


public class GalleryItem extends Application implements Constants, Parcelable {

    private long id, fromUserId;
    private int createAt, likesCount, viewsCount, commentsCount, accessMode, fromUserVerified, fromUserAllowGalleryComments, fromUserAllowShowOnline;
    private String timeAgo, date, desc, imgUrl, previewImgUrl, originImgUrl, fromUserUsername, fromUserFullname, fromUserPhotoUrl, area, country, city;
    private String videoUrl, previewVideoImgUrl;
    private int itemType;
    private Double lat = 0.000000, lng = 0.000000;
    private Boolean myLike = false;
    private Boolean fromUserOnline = false;

    public GalleryItem() {

    }

    public GalleryItem(JSONObject jsonData) {

        try {

            if (!jsonData.getBoolean("error")) {

                this.setId(jsonData.getLong("id"));
                this.setFromUserId(jsonData.getLong("fromUserId"));
                this.setAccessMode(jsonData.getInt("accessMode"));
                this.setItemType(jsonData.getInt("itemType"));
                this.setFromUserAllowGalleryComments(jsonData.getInt("fromUserAllowGalleryComments"));
                this.setFromUserAllowShowOnline(jsonData.getInt("fromUserAllowShowOnline"));
                this.setFromUserVerified(jsonData.getInt("fromUserVerified"));
                this.setFromUserOnline(jsonData.getBoolean("fromUserOnline"));
                this.setFromUserUsername(jsonData.getString("fromUserUsername"));
                this.setFromUserFullname(jsonData.getString("fromUserFullname"));
                this.setFromUserPhotoUrl(jsonData.getString("fromUserPhoto"));
                this.setDescription(jsonData.getString("desc"));
                this.setVideoUrl(jsonData.getString("videoUrl"));
                this.setPreviewVideoImgUrl(jsonData.getString("previewVideoImgUrl"));
                this.setImgUrl(jsonData.getString("imgUrl"));
                this.setPreviewImgUrl(jsonData.getString("previewImgUrl"));
                this.setOriginImgUrl(jsonData.getString("originImgUrl"));
                this.setArea(jsonData.getString("area"));
                this.setCountry(jsonData.getString("country"));
                this.setCity(jsonData.getString("city"));
                this.setCommentsCount(jsonData.getInt("commentsCount"));
                this.setLikesCount(jsonData.getInt("likesCount"));
                this.setViewsCount(jsonData.getInt("viewsCount"));
                this.setLat(jsonData.getDouble("lat"));
                this.setLng(jsonData.getDouble("lng"));
                this.setCreateAt(jsonData.getInt("createAt"));
                this.setDate(jsonData.getString("date"));
                this.setTimeAgo(jsonData.getString("timeAgo"));

                this.setMyLike(jsonData.getBoolean("myLike"));
            }

        } catch (Throwable t) {

            Log.e("Gallery Item", "Could not parse malformed JSON: \"" + jsonData.toString() + "\"");

        } finally {

            Log.d("Gallery Item", jsonData.toString());
        }
    }


    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public long getFromUserId() {

        return fromUserId;
    }

    public void setFromUserId(long fromUserId) {

        this.fromUserId = fromUserId;
    }

    public int getAccessMode() {

        return accessMode;
    }

    public void setAccessMode(int accessMode) {

        this.accessMode = accessMode;
    }

    public int getItemType() {

        return itemType;
    }

    public void setItemType(int itemType) {

        this.itemType = itemType;
    }

    public int getFromUserVerified() {

        return fromUserVerified;
    }

    public void setFromUserVerified(int fromUserVerified) {

        this.fromUserVerified = fromUserVerified;
    }

    public Boolean getFromUserOnline() {

        return fromUserOnline;
    }

    public void setFromUserOnline(Boolean fromUserOnline) {

        this.fromUserOnline = fromUserOnline;
    }

    public int getFromUserAllowGalleryComments() {

        return fromUserAllowGalleryComments;
    }

    public void setFromUserAllowGalleryComments(int fromUserAllowGalleryComments) {

        this.fromUserAllowGalleryComments = fromUserAllowGalleryComments;
    }

    public int getFromUserAllowShowOnline() {

        return fromUserAllowShowOnline;
    }

    public void setFromUserAllowShowOnline(int fromUserAllowShowOnline) {

        this.fromUserAllowShowOnline = fromUserAllowShowOnline;
    }

    public int getCommentsCount() {

        return commentsCount;
    }

    public void setCommentsCount(int commentsCount) {
        this.commentsCount = commentsCount;
    }

    public int getLikesCount() {

        return likesCount;
    }

    public void setLikesCount(int likesCount) {
        this.likesCount = likesCount;
    }

    public int getViewsCount() {

        return viewsCount;
    }

    public void setViewsCount(int viewsCount) {

        this.viewsCount = viewsCount;
    }

    public int getCreateAt() {

        return createAt;
    }

    public void setCreateAt(int createAt) {
        this.createAt = createAt;
    }

    public String getTimeAgo() {
        return timeAgo;
    }

    public void setTimeAgo(String timeAgo) {
        this.timeAgo = timeAgo;
    }

    public String getDescription() {
        return this.desc;
    }

    public void setDescription(String desc) {
        this.desc = desc;
    }


    public String getFromUserUsername() {
        return fromUserUsername;
    }

    public void setFromUserUsername(String fromUserUsername) {
        this.fromUserUsername = fromUserUsername;
    }

    public String getFromUserFullname() {
        return fromUserFullname;
    }

    public void setFromUserFullname(String fromUserFullname) {
        this.fromUserFullname = fromUserFullname;
    }

    public String getFromUserPhotoUrl() {

        if (fromUserPhotoUrl == null) {

            fromUserPhotoUrl = "";
        }

        return fromUserPhotoUrl;
    }

    public void setFromUserPhotoUrl(String fromUserPhotoUrl) {

        this.fromUserPhotoUrl = fromUserPhotoUrl;
    }

    public String getVideoUrl() {

        return videoUrl;
    }

    public void setVideoUrl(String videoUrl) {
        this.videoUrl = videoUrl;
    }

    public String getPreviewVideoImgUrl() {

        return previewVideoImgUrl;
    }

    public void setPreviewVideoImgUrl(String previewVideoImgUrl) {

        this.previewVideoImgUrl = previewVideoImgUrl;
    }

    public String getImgUrl() {
        return imgUrl;
    }

    public void setImgUrl(String imgUrl) {
        this.imgUrl = imgUrl;
    }

    public String getPreviewImgUrl() {
        return previewImgUrl;
    }

    public void setPreviewImgUrl(String previewImgUrl) {
        this.previewImgUrl = previewImgUrl;
    }

    public String getOriginImgUrl() {
        return originImgUrl;
    }

    public void setOriginImgUrl(String originImgUrl) {
        this.originImgUrl = originImgUrl;
    }

    public String getDate() {
        return date;
    }

    public void setDate(String date) {
        this.date = date;
    }

    public String getArea() {

        if (this.area == null) {

            this.area = "";
        }

        return this.area;
    }

    public void setArea(String area) {

        this.area = area;
    }

    public String getCountry() {

        if (this.country == null) {

            this.country = "";
        }

        return this.country;
    }

    public void setCountry(String country) {

        this.country = country;
    }

    public String getCity() {

        if (this.city == null) {

            this.city = "";
        }

        return this.city;
    }

    public void setCity(String city) {

        this.city = city;
    }

    public Double getLat() {

        return this.lat;
    }

    public void setLat(Double lat) {

        this.lat = lat;
    }

    public Double getLng() {

        return this.lng;
    }

    public void setLng(Double lng) {

        this.lng = lng;
    }

    public Boolean isMyLike() {
        return myLike;
    }

    public void setMyLike(Boolean myLike) {

        this.myLike = myLike;
    }

    @Override
    public int describeContents() {

        return 0;
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {

    }
    public static final Creator CREATOR = new Creator() {

        public GalleryItem createFromParcel(Parcel in) {

            return new GalleryItem();
        }

        public GalleryItem[] newArray(int size) {
            return new GalleryItem[size];
        }
    };
}
