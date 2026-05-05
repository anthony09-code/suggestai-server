<?php

namespace App\Console\Commands;

use App\Models\Office;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature("offices:generate-qrcodes")]
#[Description("Generate QR codes for all offices that do not have one")]
class GenerateOfficeQrCodes extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $offices = Office::query()
            ->whereNull("qr_code")
            ->orWhere("qr_code", "")
            ->get();

        if ($offices->isEmpty()) {
            $this->info("All offices already have QR codes.");
            return;
        }

        $this->info("Found {$offices->count()} offices without QR codes.");
        $bar = $this->output->createProgressBar($offices->count());
        $bar->start();

        foreach ($offices as $office) {
            try {
                $url = url("/student/feedback/{$office->access_link}");
                $qrCode = new QrCode($url);
                $writer = new SvgWriter();
                $result = $writer->write($qrCode);
                $filename = "qrcodes/office-{$office->id}.svg";

                Storage::disk("public")->put($filename, $result->getString());

                $office->qr_code = $filename;
                $office->saveQuietly();

                $bar->advance();
            } catch (\Exception $e) {
                $this->newLine();
                $this->error(
                    "Failed for {$office->office_name}: {$e->getMessage()}",
                );
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("QR codes generated successfully!");
    }
}
