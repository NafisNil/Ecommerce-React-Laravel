<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Models\Payout;
use Illuminate\Console\Command;
use App\Models\Vendor;
use App\Models\Order;
use DB;
class PayoutVendors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payout:vendors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process payouts for vendors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $this->info('Payouts processed successfully.');
        $vendors = Vendor::eligibleForPayout()->get();
        foreach ($vendors as $vendor) {
            // Logic to process payout for each vendor
            $this->processPayout($vendor);
        }
        $this->info('All eligible vendors have been processed for payouts.');
        return Command::SUCCESS;
    }

    protected function processPayout(Vendor $vendor)
    {
        // Implement payout logic here
        $this->info("Processing payout for Vendor ID: {$vendor->id}...Shop Name: {$vendor->shop_name}");
        try {
            // Example: Create a Payout record, send notification, etc.
            DB::beginTransaction();
            $startingFrom = Payout::where('vendor_id', $vendor->user_id)->orderBy('until', 'desc')->value('until') ?? now()->subMonth();
            $until = now()->subMonthNoOverflow()->endOfMonth();
            $vendorSubtotal = Order::query()
                ->where('vendor_user_id', $vendor->user_id)
                ->where('status', OrderStatusEnum::Paid->value)
                ->whereBetween('created_at', [$startingFrom, $until])
                ->sum('vendor_subtotal');
            if ($vendorSubtotal > 0) {
                Payout::create([
                    'vendor_id' => $vendor->user_id,
                    'amount' => $vendorSubtotal,
                    'starting_from' => $startingFrom,
                    'until' => $until,
                    'status' => 'pending',
                ]);
            }else {
                $this->info("No eligible orders for payout for Vendor ID: {$vendor->id} in the period from {$startingFrom} to {$until}.");
            }
            DB::commit();
            $this->info("Payout processed for Vendor ID: {$vendor->id}. Amount: {$vendorSubtotal}");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to process payout for Vendor ID: {$vendor->id}. Error: {$e->getMessage()}");
        }
    }
}
