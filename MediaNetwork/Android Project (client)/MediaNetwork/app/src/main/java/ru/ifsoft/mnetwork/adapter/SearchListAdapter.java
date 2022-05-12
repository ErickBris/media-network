package ru.ifsoft.mnetwork.adapter;

import android.content.Context;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.balysv.materialripple.MaterialRippleLayout;
import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

import java.util.List;

import ru.ifsoft.mnetwork.R;
import ru.ifsoft.mnetwork.model.Profile;


public class SearchListAdapter extends RecyclerView.Adapter<SearchListAdapter.MyViewHolder> {

	private Context mContext;
	private List<Profile> itemList;

    private OnItemClickListener mOnItemClickListener;

    public interface OnItemClickListener {

        void onItemClick(View view, Profile obj, int position);
    }

    public void setOnItemClickListener(final OnItemClickListener mItemClickListener) {

        this.mOnItemClickListener = mItemClickListener;
    }

	public class MyViewHolder extends RecyclerView.ViewHolder {

		public TextView mProfileFullname, mProfileUsername;
		public ImageView mProfilePhoto, mProfileOnlineIcon;
		public MaterialRippleLayout mParent;

		public MyViewHolder(View view) {

			super(view);

			mParent = (MaterialRippleLayout) view.findViewById(R.id.parent);

			mProfilePhoto = (ImageView) view.findViewById(R.id.profilePhoto);
			mProfileFullname = (TextView) view.findViewById(R.id.profileFullname);
			mProfileUsername = (TextView) view.findViewById(R.id.profileUsername);
            mProfileOnlineIcon = (ImageView) view.findViewById(R.id.profileOnlineIcon);
		}
	}


	public SearchListAdapter(Context mContext, List<Profile> itemList) {

		this.mContext = mContext;
		this.itemList = itemList;
	}

	@Override
	public MyViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {

		View itemView = LayoutInflater.from(parent.getContext()).inflate(R.layout.profile_thumbnail, parent, false);


		return new MyViewHolder(itemView);
	}

	@Override
	public void onBindViewHolder(final MyViewHolder holder, final int position) {

		final Profile item = itemList.get(position);

		holder.mProfileFullname.setText(item.getFullname());
        holder.mProfileUsername.setText("@" + item.getUsername());

        if (item.getPhotoUrl() != null && item.getPhotoUrl().length() > 0) {

            Glide.with(mContext).load(item.getPhotoUrl())
                    .thumbnail(0.5f)
                    .crossFade()
                    .diskCacheStrategy(DiskCacheStrategy.ALL)
                    .into(holder.mProfilePhoto);

        } else {

			holder.mProfilePhoto.setImageResource(R.drawable.profile_default_photo);
		}

		if (item.isOnline() && item.getAllowShowOnline() == 1) {

			holder.mProfileOnlineIcon.setVisibility(View.VISIBLE);

		} else {

            holder.mProfileOnlineIcon.setVisibility(View.GONE);
		}

        holder.mParent.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View view) {

                if (mOnItemClickListener != null) {

                    mOnItemClickListener.onItemClick(view, item, position);
                }
            }
        });
	}

	@Override
	public int getItemCount() {

		return itemList.size();
	}
}