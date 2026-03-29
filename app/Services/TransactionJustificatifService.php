<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionJustificatifService
{
    public const MAX_KB = 5120;

    /** Règles de validation Laravel pour le fichier « justificatif ». */
    public static function validationRules(string $attribute = 'justificatif'): array
    {
        return [
            $attribute => 'nullable|file|max:'.self::MAX_KB.'|mimes:jpeg,jpg,png,webp,pdf',
        ];
    }

    public function storeUploadedFile(Transaction $transaction, UploadedFile $file): string
    {
        $this->deleteStoredIfAny($transaction);

        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'pdf'], true)) {
            $ext = 'jpg';
        }

        $name = Str::uuid()->toString().'.'.$ext;

        return $file->storeAs('justificatifs', $name, 'local');
    }

    public function deleteStoredIfAny(?Transaction $transaction): void
    {
        if (! $transaction || empty($transaction->photo_justificatif)) {
            return;
        }

        $path = $transaction->photo_justificatif;
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }
}
