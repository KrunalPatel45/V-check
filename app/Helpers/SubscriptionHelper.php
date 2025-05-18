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
        $newPriceId = $data['new_price_id'];

        // 1. Get the subscription
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

        // 2. Update the subscription item with the new price
        $update = Http::withToken(config('services.stripe.secret'))
            ->asForm()
            ->post("https://api.stripe.com/v1/subscription_items/{$itemId}", [
                'price' => $newPriceId,
                'proration_behavior' => 'always_invoice',
                // Remove 'billing_cycle_anchor' if it causes 400 error
            ]);

        if (!$update->successful()) {
            return false;
        }

        // 3. Fetch latest invoice
        $invoices = Http::withToken(config('services.stripe.secret'))
            ->get("https://api.stripe.com/v1/invoices", [
                'subscription' => $subscriptionId,
                'limit' => 1,
            ]);

        if (!$invoices->successful()) {
            return false;
        }

        $latestInvoice = $invoices->json('data.0');

        if (!empty($latestInvoice) && $latestInvoice['amount_due'] > 0) {
           return !empty($latestInvoice['id']) ? $latestInvoice['id'] : false;
        }

        return false;
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

        $defaultPaymentMethodId = $customer['invoice_settings']['default_payment_method'] ?? null;

        if ($defaultPaymentMethodId) {
            // Fetch the payment method details (card brand, last4, etc.)
            $pmResponse = Http::withToken(config('services.stripe.secret'))
                ->get("https://api.stripe.com/v1/payment_methods/" . $defaultPaymentMethodId);

            $data = $pmResponse->json();
            return !empty($data['id']) ? $data['id'] : null;
        }

        return null;
    }

    public function cancelAtPeriodEnd($subscriptionId)
    {
        $response = Http::withToken(config('services.stripe.secret'))
            ->asForm()
            ->post("https://api.stripe.com/v1/subscriptions/{$subscriptionId}", [
                'cancel_at_period_end' => 'true',
            ]);

        if ($response->successful()) {
            return true;
        }

        return false;
    }

    public function schedulePlanDowngrade($subscriptionId, $newPriceId)
    {
        $stripeSecret = config('services.stripe.secret');

        // Step 1: Retrieve the current subscription
        $subscriptionResponse = Http::withToken($stripeSecret)
            ->get("https://api.stripe.com/v1/subscriptions/{$subscriptionId}");

        if (!$subscriptionResponse->successful()) {
           return false;
        }

        $subscription = $subscriptionResponse->json();

        // Extract necessary details
        $currentPriceId = $subscription['items']['data'][0]['price']['id'] ?? null;
        $currentPeriodEnd = $subscription['items']['data'][0]['current_period_end'] ?? null;

        if (!$currentPriceId || !$currentPeriodEnd) {
             return false;
        }

        // Step 2: Create a Subscription Schedule from the existing subscription
        $scheduleResponse = Http::withToken($stripeSecret)
            ->asForm()
            ->post("https://api.stripe.com/v1/subscription_schedules", [
                'from_subscription' => $subscriptionId,
            ]);

        if (!$scheduleResponse->successful()) {
           return false;
        }

        $schedule = $scheduleResponse->json();
        $scheduleId = $schedule['id'];

        // Step 3: Update the Subscription Schedule to add a new phase for the downgrade
        $updateResponse = Http::withToken($stripeSecret)
            ->asForm()
            ->post("https://api.stripe.com/v1/subscription_schedules/{$scheduleId}", [
                // Phase 1: Current plan until current_period_end
                'phases[0][start_date]' => $subscription['start_date'],
                'phases[0][end_date]' => $currentPeriodEnd,
                'phases[0][items][0][price]' => $currentPriceId,
                'phases[0][items][0][quantity]' => 1,

                // Phase 2: Downgrade plan after current_period_end
                'phases[1][start_date]' => $currentPeriodEnd,
                'phases[1][items][0][price]' => $newPriceId,
                'phases[1][items][0][quantity]' => 1,
                'phases[1][iterations]' => 1,
            ]);

        if (!$updateResponse->successful()) {
            false;
        }

        return $updateResponse->json();
    }

}