package ru.ifsoft.mnetwork.util;

import android.app.Application;
import android.content.Context;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.drawable.BitmapDrawable;
import android.graphics.drawable.Drawable;
import android.net.Uri;
import android.provider.MediaStore;
import android.util.Log;
import android.widget.ImageView;
import android.widget.Toast;

import com.android.volley.Request;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.ImageLoader;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;

import ru.ifsoft.mnetwork.R;
import ru.ifsoft.mnetwork.app.App;
import ru.ifsoft.mnetwork.constants.Constants;
import ru.ifsoft.mnetwork.model.GalleryItem;

public class Api extends Application implements Constants {

    Context context;

    public Api (Context context) {

        this.context = context;
    }

    public void itemShare(GalleryItem item) {

        String shareText = "";

        if (item.getDescription().length() > 0) {

            shareText = item.getDescription() + " \n\n" + context.getString(R.string.app_share_text);

        } else {

            shareText = context.getString(R.string.app_share_text);
        }


        Intent shareIntent = new Intent(android.content.Intent.ACTION_SEND);

        shareIntent.putExtra(android.content.Intent.EXTRA_SUBJECT, (String) context.getString(R.string.app_name));
        shareIntent.putExtra(android.content.Intent.EXTRA_TEXT, shareText);

        if (item.getImgUrl().length() > 0) {

            ImageView image;
            ImageLoader imageLoader = App.getInstance().getImageLoader();

            image = new ImageView(context);

            if (imageLoader == null) {

                imageLoader = App.getInstance().getImageLoader();
            }

            imageLoader.get(item.getImgUrl(), ImageLoader.getImageListener(image, R.drawable.profile_default_photo, R.drawable.profile_default_photo));

            Drawable mDrawable = image.getDrawable();
            Bitmap mBitmap = ((BitmapDrawable)mDrawable).getBitmap();

            String path = MediaStore.Images.Media.insertImage(context.getContentResolver(), mBitmap, "Image Description", null);

            Uri uri = Uri.parse(path);

            shareIntent.putExtra(android.content.Intent.EXTRA_STREAM, uri);
        }

        shareIntent.setType("text/plain");

        context.startActivity(Intent.createChooser(shareIntent, "Item post"));
    }

    public void itemDelete(final long itemId) {

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_ITEMS_DELETE, null,
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

                Log.e("", error.toString());
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());
                params.put("itemId", Long.toString(itemId));

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }

    public void commentDelete(final long itemId, final int itemType) {

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_COMMENT_REMOVE, null,
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

                Log.e("", error.toString());
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());
                params.put("itemId", Long.toString(itemId));
                params.put("itemType", Long.toString(itemType));

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }

    public void report(final long itemId, final int itemType, final int reasonId) {

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_REPORT_ADD, null,
                new Response.Listener<JSONObject>() {
                    @Override
                    public void onResponse(JSONObject response) {

                        try {

                            if (!response.getBoolean("error")) {


                            }

                        } catch (JSONException e) {

                            e.printStackTrace();

                        } finally {

                            Toast.makeText(context, context.getString(R.string.label_item_reported), Toast.LENGTH_SHORT).show();
                        }
                    }
                }, new Response.ErrorListener() {
            @Override
            public void onErrorResponse(VolleyError error) {

                Log.e("", error.toString());
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());
                params.put("toItemType", Integer.toString(itemType));
                params.put("toItemId", Long.toString(itemId));
                params.put("abuseId", Integer.toString(reasonId));

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }

    public void acceptFriendRequest(final long fromUser) {

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_FRIENDS_ACCEPT, null,
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

                Log.e("Api.acceptFriendRequest", error.toString());
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());
                params.put("fromUser", Long.toString(fromUser));

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }

    public void rejectFriendRequest(final long fromUser) {

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_FRIENDS_REJECT, null,
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

                Log.e("Api.rejectFriendRequest", error.toString());
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());
                params.put("fromUser", Long.toString(fromUser));

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }

    public void galleryItemDelete(final long itemId) {

        CustomRequest jsonReq = new CustomRequest(Request.Method.POST, METHOD_GALLERY_REMOVE_ITEM, null,
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

                Log.e("", error.toString());
            }
        }) {

            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<String, String>();
                params.put("accountId", Long.toString(App.getInstance().getId()));
                params.put("accessToken", App.getInstance().getAccessToken());
                params.put("itemId", Long.toString(itemId));

                return params;
            }
        };

        App.getInstance().addToRequestQueue(jsonReq);
    }
}
