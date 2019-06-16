<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Twilio\Rest\Client;

class ProductController extends Controller
{

    /**
     * Verify a product.
     *
     * @param  Request  $request
     * @return Response
     */
    public function verify(Request $request)
    {
        $from = $request->input("From");
        $body = $request->input("Body");

        $product = Product::where("product_id", $body)->first();
        if (!$product) {
            $product_status = "Product not available";
        } else if ($product->is_original) {
            $product_status = "Original '$product->name', BUY NOW!!";
        } else {
            $product_status = "Fake '$product->name', DON'T BUY!!";
        }

        $this->sendMessage($product_status, $from);
        return "message received";
    }

    /**
     * Sends sms to user using Twilio's programmable sms client
     * @param String $message Body of sms
     * @param Number $recipients Number of recepient
     */
    private function sendMessage($message, $recipients)
    {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_NUMBER");

        $client = new Client($account_sid, $auth_token);
        $client->messages->create($recipients, array('from' => $twilio_number, 'body' => $message));
    }
}
