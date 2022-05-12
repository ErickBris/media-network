package ru.ifsoft.mnetwork;

import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v7.widget.Toolbar;
import android.view.MenuItem;

import ru.ifsoft.mnetwork.common.ActivityBase;
import ru.ifsoft.mnetwork.dialogs.CommentActionDialog;
import ru.ifsoft.mnetwork.dialogs.CommentDeleteDialog;
import ru.ifsoft.mnetwork.dialogs.ItemDeleteDialog;
import ru.ifsoft.mnetwork.dialogs.ItemReportDialog;


public class ViewGalleryItemActivity extends ActivityBase implements CommentDeleteDialog.AlertPositiveListener, CommentActionDialog.AlertPositiveListener, ItemDeleteDialog.AlertPositiveListener, ItemReportDialog.AlertPositiveListener {

    Toolbar mToolbar;

    Fragment fragment;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_view_gallery_item);

        mToolbar = (Toolbar) findViewById(R.id.toolbar);

        setSupportActionBar(mToolbar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setHomeButtonEnabled(true);

        if (savedInstanceState != null) {

            fragment = getSupportFragmentManager().getFragment(savedInstanceState, "currentFragment");

        } else {

            fragment = new ViewGalleryItemFragment();
        }

        FragmentManager fragmentManager = getSupportFragmentManager();
        fragmentManager.beginTransaction().replace(R.id.container_body, fragment).commit();
    }

    @Override
    protected void onSaveInstanceState (Bundle outState) {

        super.onSaveInstanceState(outState);

        getSupportFragmentManager().putFragment(outState, "currentFragment", fragment);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {

        super.onActivityResult(requestCode, resultCode, data);

        fragment.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    protected void onPause() {

        ViewGalleryItemFragment p = (ViewGalleryItemFragment) fragment;
        p.hideEmojiKeyboard();

        super.onPause();
    }

    @Override
    public void onItemDelete(int position) {

        ViewGalleryItemFragment p = (ViewGalleryItemFragment) fragment;
        p.onItemDelete(position);
    }

    @Override
    public void onItemReport(int position, int reasonId) {

        ViewGalleryItemFragment p = (ViewGalleryItemFragment) fragment;
        p.onItemReport(position, reasonId);
    }

    @Override
    public void onCommentRemove(int position) {

        ViewGalleryItemFragment p = (ViewGalleryItemFragment) fragment;
        p.onCommentRemove(position);
    }

    @Override
    public void onCommentDelete(int position) {

        ViewGalleryItemFragment p = (ViewGalleryItemFragment) fragment;
        p.onCommentDelete(position);
    }

    @Override
    public void onCommentReply(int position) {

        ViewGalleryItemFragment p = (ViewGalleryItemFragment) fragment;
        p.onCommentReply(position);
    }

    @Override
    public void onBackPressed(){

        finish();
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.

        switch (item.getItemId()) {

            case android.R.id.home: {

                finish();
                return true;
            }

            default: {

                return super.onOptionsItemSelected(item);
            }
        }
    }
}
