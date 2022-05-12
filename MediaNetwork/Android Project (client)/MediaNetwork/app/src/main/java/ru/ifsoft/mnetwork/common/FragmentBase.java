package ru.ifsoft.mnetwork.common;

import android.app.ProgressDialog;
import android.os.Bundle;
import android.support.v4.app.Fragment;

import ru.ifsoft.mnetwork.R;
import ru.ifsoft.mnetwork.constants.Constants;

public class FragmentBase extends Fragment implements Constants {

    public static final String TAG = "ActivityBase";

    private ProgressDialog pDialog;

    @Override
    public void onCreate(Bundle savedInstanceState) {

        super.onCreate(savedInstanceState);

        initpDialog();
    }

    protected void initpDialog() {

        pDialog = new ProgressDialog(getActivity());
        pDialog.setMessage(getString(R.string.msg_loading));
        pDialog.setCancelable(false);
    }

    protected void showpDialog() {

        if (!pDialog.isShowing()) {

            try {

                pDialog.show();

            } catch (Exception e) {

                e.printStackTrace();
            }
        }
    }

    protected void hidepDialog() {

        if (pDialog.isShowing()) {

            try {

                pDialog.dismiss();

            } catch (Exception e) {

                e.printStackTrace();
            }
        }
    }
}
