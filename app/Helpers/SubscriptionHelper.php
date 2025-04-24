<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class SubscriptionHelper {

    public function addPlan($data)
    {

        $product = $this->addProduct($data['name']);

        if(!empty($product['id'])) {
            $response = Http::withBasicAuth(config('services.stripe.secret'), '')
                ->asForm()
                ->post('https://api.stripe.com/v1/plans', [
                    'amount' => $data['price'] * 100,
                    'currency' => 'usd',
                    'interval' => 'month',
                    'product' => $product['id'],
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
               return false;
            }
        }

    }

    public function addProduct($name)
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
        ->asForm()
        ->post('https://api.stripe.com/v1/products', [
            'name' => $name,
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            return false;
        }
    }

    public function deleteProduct($productId)
    {

        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
            ->delete("https://api.stripe.com/v1/products/{$productId}");

        if ($response->successful()) {
            return $response->json();
        } else {
            return false;
        }
    }


    public function deletePlan($planId)
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
            ->delete("https://api.stripe.com/v1/plans/{$planId}");

        if ($response->successful()) {
            return $response->json();
        } else {
            return false;
        }
    }

    public function addCustomer($data)
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
                ->asForm()
                ->post('https://api.stripe.com/v1/customers', [
                    'name' => $data['name'],
                    'email' => $data['email'],
                ]);

            if ($response->successful()) {
                return $response->json();
            } else {
               return false;
            }
    }

    public function addPrice($product, $amount)
    {
        $response = Http::withBasicAuth(config('services.stripe.secret'), '')
                ->asForm()
                ->post('https://api.stripe.com/v1/prices', [
                    'currency' => 'usd',
                    'unit_amount' => $amount * 100,
                    'recurring[interval]' => 'month',
                    'product' => $product
                ]);
            

            if ($response->successful()) {
                return $response->json();
            } else {
               return false;
            }
    }

    public function addSubscription($data)
    {
        $session = Http::withToken(config('services.stripe.secret'))->asForm()->post('https://api.stripe.com/v1/checkout/sessions', [
            'customer' => $data['cusID'],
            'payment_method_types[]' => 'card',
            'line_items[0][price]' => $data['price_id'] ,
            'line_items[0][quantity]' => 1,
            'mode' => 'subscription',
            'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}&user=' . md5($data['user_id']),
            'cancel_url' => route('stripe.cancel'),
        ]);

        if (!$session->successful()) {
            return false;
        }

        return $session->json();
    }


    public function updateSubscription($data)
    {
        $subscriptionId = $data['subscription_id'];
        $newPriceId     = $data['new_price_id'];

        // 1. Get subscription to find the item ID
        $subscription = Http::withToken(config('services.stripe.secret'))
            ->get("https://api.stripe.com/v1/subscriptions/{$subscriptionId}");

        if (!$subscription->successful()) {
            return false;
        }

        $items = $subscription->json('items.data');
        if (empty($items)) {
            return false;
        }

        $itemId = $items[0]['id'];

        // 2. Update subscription item with new plan and charge difference immediately
        $update = Http::withToken(config('services.stripe.secret'))
            ->asForm()
            ->post("https://api.stripe.com/v1/subscription_items/{$itemId}", [
                'price'              => $newPriceId,
                'proration_behavior' => 'always_invoice', // charge $5 difference immediately
            ]);

        if (!$update->successful()) {
            return false;
        }

        return $update->json();
    }

    public function getCustomerPaymentMethods($customerId)
    {
        $response = Http::withToken(config('services.stripe.secret'))
        ->get("https://api.stripe.com/v1/payment_methods", [
            'customer' => $customerId,
            'type' => 'card',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return false;
    }

    public function getDefaultCard($id)
    {
        $customerResponse = Http::withToken(config('services.stripe.secret'))
        ->get("https://api.stripe.com/v1/customers/" . $id);

        $customer = $customerResponse->json();

        $defaultSourceId = $customer['default_source'] ?? null;
        return $defaultSourceId;
    }

}