package ru.ifsoft.mnetwork.adapter;

import android.content.Context;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import com.balysv.materialripple.MaterialRippleLayout;
import com.bumptech.glide.Glide;
import com.bumptech.glide.load.engine.DiskCacheStrategy;

import java.util.List;

import ru.ifsoft.mnetwork.R;
import ru.ifsoft.mnetwork.constants.Constants;
import ru.ifsoft.mnetwork.model.GalleryItem;


public class GalleryListAdapter extends RecyclerView.Adapter<GalleryListAdapter.MyViewHolder> {

    private List<GalleryItem> images;
    private Context mContext;

    private OnItemClickListener mOnItemClickListener;

    public interface OnItemClickListener {

        void onItemClick(View view, GalleryItem obj, int position);
    }

    public void setOnItemClickListener(final OnItemClickListener mItemClickListener) {

        this.mOnItemClickListener = mItemClickListener;
    }

    public class MyViewHolder extends RecyclerView.ViewHolder {

        public ImageView thumbnail;
        public ImageView playImg;
        public MaterialRippleLayout mParent;

        public MyViewHolder(View view) {

            super(view);

            mParent = (MaterialRippleLayout) view.findViewById(R.id.parent);
            thumbnail = (ImageView) view.findViewById(R.id.thumbnail);
            playImg = (ImageView) view.findViewById(R.id.playImg);
        }
    }


    public GalleryListAdapter(Context context, List<GalleryItem> images) {

        mContext = context;
        this.images = images;
    }

    @Override
    public MyViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {

        View itemView = LayoutInflater.from(parent.getContext()).inflate(R.layout.gallery_thumbnail, parent, false);

        return new MyViewHolder(itemView);
    }

    @Override
    public void onBindViewHolder(MyViewHolder holder, final int position) {

        final GalleryItem item = images.get(position);

        if (item.getItemType() == Constants.GALLERY_ITEM_TYPE_VIDEO) {

            holder.playImg.setVisibility(View.VISIBLE);

        } else {

            holder.playImg.setVisibility(View.GONE);
        }

        Glide.with(mContext).load(item.getImgUrl())
                .thumbnail(0.5f)
                .crossFade()
                .diskCacheStrategy(DiskCacheStrategy.ALL)
                .into(holder.thumbnail);

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

        return images.size();
    }
}