<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Donation;
use Veritrans_Config;
use Veritrans_Snap;
use Veritrans_Notification;

class DonationController extends Controller
{

    public function index()
    {
        return view('donation');
    }

    public function store (Request $request)
    {
        \DB::transaction(function () use ($request){
            $donation = Donation::create([
                'donor_name'    => $request->donor_name,
                'donor_email'   => $request->donor_email,
                'donation_type' => $request->donation_type,
                'amount'        => floatval($request->amount),
                'note'          => $request->note
            ]);

            $payload = [
                'transaction_details'   => [
                    'order_id'  => 'SANDBOX-' . uniqid(),
                    'gross_amount'  => $donation->amount,
                ],
                'customer_details' => [
                    'first_name'    => $donation->name,
                    'email'         => $donation->email,

                ],
                'item_details'  => [
                    [
                    'id'        => $donation->donation_type,
                    'price'     => $donation->amount,
                    'quantity'  => 1,
                    'name'      => ucwords(str_replace('_', '', $donation->donation_tpe))
                    ]
                ]
            ];
            $snapToken = Veritrans_Snap::getSnapToken($payload);
            $donation->snap_token = $snapToken;
            $donation->save();

            $this->response['snap_token']  = $snapToken;
        });

        return response()->json($this->response);
    }
}
