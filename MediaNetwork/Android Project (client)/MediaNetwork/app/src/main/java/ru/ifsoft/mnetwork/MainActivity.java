package ru.ifsoft.mnetwork;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.annotation.IdRes;
import android.support.design.widget.NavigationView;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.widget.DrawerLayout;
import android.support.v7.app.ActionBarDrawerToggle;
import android.support.v7.widget.Toolbar;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.view.inputmethod.InputMethodManager;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.android.volley.toolbox.ImageLoader;
import com.google.android.gms.ads.AdRequest;
import com.google.android.gms.ads.AdView;
import com.pkmmte.view.CircularImageView;

import ru.ifsoft.mnetwork.app.App;
import ru.ifsoft.mnetwork.common.ActivityBase;
import ru.ifsoft.mnetwork.dialogs.FriendRequestActionDialog;
import ru.ifsoft.mnetwork.dialogs.ImageChooseDialog;
import ru.ifsoft.mnetwork.dialogs.NearbySettingsDialog;
import ru.ifsoft.mnetwork.dialogs.ProfileBlockDialog;
import ru.ifsoft.mnetwork.dialogs.ProfileReportDialog;
import ru.ifsoft.mnetwork.dialogs.SearchSettingsDialog;

public class MainActivity extends ActivityBase implements ImageChooseDialog.AlertPositiveListener, ProfileReportDialog.AlertPositiveListener, ProfileBlockDialog.AlertPositiveListener, NearbySettingsDialog.AlertPositiveListener, SearchSettingsDialog.AlertPositiveListener, FriendRequestActionDialog.AlertPositiveListener {

    Toolbar mToolbar;

    private NavigationView mNavView;
    private DrawerLayout mDrawerLayout;
    private Menu mNavMenu;

    private View mNavHeaderLayout;

    private TextView mNavHeaderFullname, mNavHeaderUsername;
    private CircularImageView mNavHeaderPhoto, mNavHeaderIcon;
    private ImageView mNavHeaderCover;

    AdView mAdView;
    AdRequest adRequest;

    // used to store app title
    private CharSequence mTitle;

    LinearLayout mContainerAdmob;

    Fragment fragment;
    Boolean action = false;

    private Boolean restore = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);

        setContentView(R.layout.activity_main);

        if (savedInstanceState != null) {

            //Restore the fragment's instance
            fragment = getSupportFragmentManager().getFragment(savedInstanceState, "currentFragment");

            restore = savedInstanceState.getBoolean("restore");
            mTitle = savedInstanceState.getString("mTitle");

        } else {

            fragment = new StreamFragment();

            restore = false;
            mTitle = getString(R.string.app_name);
        }

        if (fragment != null) {

            FragmentManager fragmentManager = getSupportFragmentManager();
            fragmentManager.beginTransaction().replace(R.id.container_body, fragment).commit();
        }

        mToolbar = (Toolbar) findViewById(R.id.toolbar);

        setSupportActionBar(mToolbar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        getSupportActionBar().setHomeButtonEnabled(true);
        getSupportActionBar().setTitle(mTitle);

        mContainerAdmob = (LinearLayout) findViewById(R.id.container_admob);

        mAdView = (AdView) findViewById(R.id.adView);

        mDrawerLayout = (DrawerLayout) findViewById(R.id.drawer_layout);

        ActionBarDrawerToggle toggle = new ActionBarDrawerToggle(this, mDrawerLayout, mToolbar, R.string.nav_drawer_open, R.string.nav_drawer_close) {

            public void onDrawerOpened(View drawerView) {

                refreshMenu();

                hideKeyboard();

                updateNavItemCounter(mNavView, R.id.nav_notifications, App.getInstance().getNotificationsCount());
                updateNavItemCounter(mNavView, R.id.nav_messages, App.getInstance().getMessagesCount());
                updateNavItemCounter(mNavView, R.id.nav_friends, App.getInstance().getFriendsCount());

                super.onDrawerOpened(drawerView);
            }
        };

        mDrawerLayout.addDrawerListener(toggle);
        toggle.syncState();

        mNavView = (NavigationView) findViewById(R.id.nav_view);

        mNavHeaderLayout = mNavView.getHeaderView(0);
        mNavHeaderFullname = (TextView) mNavHeaderLayout.findViewById(R.id.fullname);
        mNavHeaderUsername = (TextView) mNavHeaderLayout.findViewById(R.id.username);

        mNavHeaderPhoto = (CircularImageView) mNavHeaderLayout.findViewById(R.id.profilePhoto);
        mNavHeaderIcon = (CircularImageView) mNavHeaderLayout.findViewById(R.id.profileIcon);
        mNavHeaderCover = (ImageView) mNavHeaderLayout.findViewById(R.id.profileCover);

        mNavView.setNavigationItemSelectedListener(new NavigationView.OnNavigationItemSelectedListener() {

            @Override
            public boolean onNavigationItemSelected(MenuItem menuItem) {

                displayFragment(menuItem.getItemId(), menuItem.getTitle().toString());
                mDrawerLayout.closeDrawers();
                return true;
            }
        });

        mNavMenu = mNavView.getMenu();

        refreshMenu();

        if (!restore) {

            // Show default section "Explore"

            displayFragment(mNavMenu.findItem(R.id.nav_stream).getItemId(), mNavMenu.findItem(R.id.nav_stream).getTitle().toString());
        }
    }

    private void refreshMenu() {

        hideAds();

        if (App.getInstance().getSettings().getNavMessagesMenuItem() == DISABLED) {

            mNavMenu.findItem(R.id.nav_messages).setVisible(false);

        } else {

            mNavMenu.findItem(R.id.nav_messages).setVisible(true);
        }

        if (App.getInstance().getSettings().getNavNotificationsMenuItem() == DISABLED) {

            mNavMenu.findItem(R.id.nav_notifications).setVisible(false);

        } else {

            mNavMenu.findItem(R.id.nav_notifications).setVisible(true);
        }

        mNavHeaderFullname.setText(App.getInstance().getFullname());
        mNavHeaderUsername.setText("@" + App.getInstance().getUsername());

        if (App.getInstance().getVerified() == 1) {

            mNavHeaderIcon.setVisibility(View.VISIBLE);

        } else {

            mNavHeaderIcon.setVisibility(View.GONE);
        }

        if (App.getInstance().getPhotoUrl() != null && App.getInstance().getPhotoUrl().length() > 0) {

            ImageLoader imageLoader = App.getInstance().getImageLoader();

            imageLoader.get(App.getInstance().getPhotoUrl(), ImageLoader.getImageListener(mNavHeaderPhoto, R.drawable.profile_default_photo, R.drawable.profile_default_photo));
        }

        if (App.getInstance().getCoverUrl() != null && App.getInstance().getCoverUrl().length() > 0) {

            ImageLoader imageLoader = App.getInstance().getImageLoader();

            imageLoader.get(App.getInstance().getCoverUrl(), ImageLoader.getImageListener(mNavHeaderCover, R.drawable.profile_default_cover, R.drawable.profile_default_cover));
        }
    }

    private void displayFragment(int id, String title) {

        action = false;

        switch (id) {

            case R.id.nav_feed: {

                mNavView.setCheckedItem(R.id.nav_feed);

                fragment = new FeedFragment();

                action = true;

                break;
            }

            case R.id.nav_stream: {

                mNavView.setCheckedItem(R.id.nav_stream);

                fragment = new StreamFragment();

                action = true;

                break;
            }

            case R.id.nav_search: {

                mNavView.setCheckedItem(R.id.nav_search);

                fragment = new SearchFragment();

                action = true;

                break;
            }

            case R.id.nav_nearby: {

                mNavView.setCheckedItem(R.id.nav_nearby);

                fragment = new ItemsNearbyFragment();

                action = true;

                break;
            }

            case R.id.nav_friends: {

                mNavView.setCheckedItem(R.id.nav_friends);

                fragment = new FriendsFragment();

                action = true;

                break;
            }

            case R.id.nav_favorites: {

                mNavView.setCheckedItem(R.id.nav_favorites);

                fragment = new FavoritesFragment();

                action = true;

                break;
            }

            case R.id.nav_notifications: {

                mNavView.setCheckedItem(R.id.nav_notifications);

                fragment = new NotificationsFragment();

                action = true;

                break;
            }

            case R.id.nav_messages: {

                mNavView.setCheckedItem(R.id.nav_messages);

                fragment = new DialogsFragment();

                action = true;

                break;
            }

            case R.id.nav_profile: {

                mNavView.setCheckedItem(R.id.nav_profile);

                fragment = new ProfileFragment();

                action = true;

                break;
            }

            case R.id.nav_settings: {

                Intent i = new Intent(MainActivity.this, SettingsActivity.class);
                startActivity(i);

                break;
            }
        }

        if (action && fragment != null) {

            getSupportActionBar().setDisplayShowCustomEnabled(false);
            getSupportActionBar().setDisplayShowTitleEnabled(true);
            getSupportActionBar().setTitle(title);

            FragmentManager fragmentManager = getSupportFragmentManager();
            fragmentManager.beginTransaction().replace(R.id.container_body, fragment).commit();
        }
    }

    private void hideKeyboard() {

        View view = this.getCurrentFocus();

        if (view != null) {

            InputMethodManager imm = (InputMethodManager) getSystemService(Context.INPUT_METHOD_SERVICE);
            imm.hideSoftInputFromWindow(view.getWindowToken(), 0);
        }
    }

    private void updateNavItemCounter(NavigationView nav, @IdRes int itemId, int count) {

        TextView view = (TextView) nav.getMenu().findItem(itemId).getActionView().findViewById(R.id.counter);
        view.setText(String.valueOf(count));

        if (count <= 0) {

            view.setVisibility(View.GONE);

        } else {

            view.setVisibility(View.VISIBLE);
        }
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {

        super.onSaveInstanceState(outState);

        outState.putBoolean("restore", true);
        outState.putString("mTitle", getSupportActionBar().getTitle().toString());
        getSupportFragmentManager().putFragment(outState, "currentFragment", fragment);
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {

        super.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, String permissions[], int[] grantResults) {

        super.onRequestPermissionsResult(requestCode, permissions, grantResults);

        fragment.onRequestPermissionsResult(requestCode, permissions, grantResults);
    }

    @Override
    public void onChangeDistance(int position) {

        ItemsNearbyFragment p = (ItemsNearbyFragment) fragment;
        p.onChangeDistance(position);
    }

    @Override
    public void onImageFromGallery() {

        ProfileFragment p = (ProfileFragment) fragment;
        p.imageFromGallery();
    }

    @Override
    public void onImageFromCamera() {

        ProfileFragment p = (ProfileFragment) fragment;
        p.imageFromCamera();
    }

    @Override
    public void onProfileReport(int position) {

        ProfileFragment p = (ProfileFragment) fragment;
        p.onProfileReport(position);
    }

    @Override
    public void onProfileBlock() {

        ProfileFragment p = (ProfileFragment) fragment;
        p.onProfileBlock();
    }

    @Override
    public void onCloseSettingsDialog(int searchGender, int searchOnline) {

        SearchFragment p = (SearchFragment) fragment;
        p.onCloseSettingsDialog(searchGender, searchOnline);
    }

    @Override
    public void onAcceptRequest(int position) {

        NotificationsFragment p = (NotificationsFragment) fragment;
        p.onAcceptRequest(position);
    }

    @Override
    public void onRejectRequest(int position) {

        NotificationsFragment p = (NotificationsFragment) fragment;
        p.onRejectRequest(position);
    }

    public void hideAds() {

        if (App.getInstance().getSettings().getAllowAdmobBanner() == DISABLED) {

            mContainerAdmob.setVisibility(View.GONE);

        } else {

            if (adRequest == null) {

                adRequest = new AdRequest.Builder().build();
                mAdView.loadAd(adRequest);
            }

            mContainerAdmob.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public boolean onPrepareOptionsMenu(Menu menu) {
        // If the nav drawer is open, hide action items related to the content view
        return super.onPrepareOptionsMenu(menu);
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {

        return super.onCreateOptionsMenu(menu);
    }

    @Override
    public boolean onOptionsItemSelected(MenuItem item) {
        // Handle action bar item clicks here. The action bar will
        // automatically handle clicks on the Home/Up button, so long
        // as you specify a parent activity in AndroidManifest.xml.
        switch (item.getItemId()) {

            case android.R.id.home: {

                return true;
            }

            default: {

                return super.onOptionsItemSelected(item);
            }
        }
    }

    @Override
    public void setTitle(CharSequence title) {

        mTitle = title;
        getSupportActionBar().setTitle(mTitle);
    }
}
