<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel as FastExcelFastExcel;

class NotificationController extends Controller
{
    function index()
    {
        $notifications = Notification::latest()->paginate(config('default_pagination'));
        return view('admin-views.notification.index', compact('notifications'));
    }

    public function store(Request $request)
    {
        if (env('APP_MODE') == 'demo') {
            return response()->json(['errors' => Helpers::error_formater('feature-disable', 'This option is disabled for demo!')]);
        }
        $validator = Validator::make($request->all(), [
            'notification_title' => 'required|max:191',
            'description' => 'required|max:1000',
            'tergat' => 'required',
            'zone'=>'required',
            'image' => 'nullable|max:2048',
        ], [
            'notification_title.required' => 'Title is required!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if ($request->has('image')) {
            $image_name = Helpers::upload(dir:'notification/', format:'png', image: $request->file('image'));
        } else {
            $image_name = null;
        }

        $notification = new Notification;
        $notification->title = $request->notification_title;
        $notification->description = $request->description;
        $notification->image = $image_name;
        $notification->tergat= $request->tergat;
        $notification->status = 1;
        $notification->zone_id = $request->zone=='all'?null:$request->zone;
        $notification->save();

        $topic_all_zone=[
            'customer'=>'all_zone_customer',
            'deliveryman'=>'all_zone_delivery_man',
            'restaurant'=>'all_zone_restaurant',
        ];

        $topic_zone_wise=[
            'customer'=>'zone_'.$request->zone.'_customer',
            'deliveryman'=>'zone_'.$request->zone.'_delivery_man',
            'restaurant'=>'zone_'.$request->zone.'_restaurant',
        ];
        $topic = $request->zone == 'all'?$topic_all_zone[$request->tergat]:$topic_zone_wise[$request->tergat];

        if($request->has('image'))
        {
            $notification->image = url('/').'/storage/app/public/notification/'.$image_name;
        }

        try {
            Helpers::send_push_notif_to_topic($notification, $topic, 'general');
        } catch (\Exception $e) {
            info($e->getMessage());
            Toastr::warning(translate('messages.push_notification_faild'));
        }

        return response()->json([], 200);
    }

    public function edit($id)
    {
        $notification = Notification::findOrFail($id);
        return view('admin-views.notification.edit', compact('notification'));
    }

    public function update(Request $request, $id)
    {
        if (env('APP_MODE') == 'demo') {
            Toastr::info(translate('messages.update_option_is_disable_for_demo'));
            return back();
        }
        $request->validate([
            'notification_title' => 'required|max:191',
            'description' => 'required|max:1000',
            'tergat' => 'required',
            'image' => 'nullable|max:2048',
        ]);

        $notification = Notification::findOrFail($id);

        if ($request->has('image')) {
            $image_name = Helpers::update(dir:'notification/', old_image: $notification->image, format: 'png', image:$request->file('image'));
        } else {
            $image_name = $notification['image'];
        }

        $notification->title = $request->notification_title;
        $notification->description = $request->description;
        $notification->image = $image_name;
        $notification->tergat= $request->tergat;
        $notification->zone_id = $request->zone=='all'?null:$request->zone;
        $notification->updated_at = now();
        $notification->save();

        $topic_all_zone=[
            'customer'=>'all_zone_customer',
            'deliveryman'=>'all_zone_delivery_man',
            'restaurant'=>'all_zone_restaurant',
        ];

        $topic_zone_wise=[
            'customer'=>'zone_'.$request->zone.'_customer',
            'deliveryman'=>'zone_'.$request->zone.'_delivery_man',
            'restaurant'=>'zone_'.$request->zone.'_restaurant',
        ];
        $topic = $request->zone == 'all'?$topic_all_zone[$request->tergat]:$topic_zone_wise[$request->tergat];
            if($image_name){
                $notification->image = url('/').'/storage/app/public/notification/'.$image_name;
            }

        try {
            Helpers::send_push_notif_to_topic($notification, $topic, 'general');
        } catch (\Exception $e) {
            info($e->getMessage());
            Toastr::warning(translate('messages.push_notification_faild'));
        }
        Toastr::success(translate('messages.notification').' '.translate('messages.updated_successfully'));
        return back();
    }

    public function status(Request $request)
    {
        $notification = Notification::findOrFail($request->id);
        $notification->status = $request->status;
        $notification?->save();
        Toastr::success(translate('messages.notification_status_updated'));
        return back();
    }

    public function delete(Request $request)
    {
        $notification = Notification::findOrFail($request->id);
        if (Storage::disk('public')->exists('notification/' . $notification['image'])) {
            Storage::disk('public')->delete('notification/' . $notification['image']);
        }
        $notification?->delete();
        Toastr::success(translate('messages.notification_deleted_successfully'));
        return back();
    }

    public function export(Request $request){
        $notifications = Notification::with('zone')->get();
        $data = Helpers::push_notification_export_data($notifications);

        if($request->type == 'csv'){
            return (new FastExcelFastExcel($data))->download('Notifications.csv');
        }
        return (new FastExcelFastExcel($data))->download('Notifications.xlsx');
    }
}
