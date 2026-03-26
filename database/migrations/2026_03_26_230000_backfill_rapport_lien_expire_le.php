<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Anciens enregistrements sans date d’expiration : on applique la règle métier 72 h après création.
     */
    public function up(): void
    {
        DB::table('rapports')->whereNull('lien_expire_le')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $created = Carbon::parse($row->created_at);
                DB::table('rapports')->where('id', $row->id)->update([
                    'lien_expire_le' => $created->copy()->addHours(72),
                ]);
            }
        });
    }

    public function down(): void
    {
        // Pas de retour en arrière : les NULL ont été remplis de façon déterministe.
    }
};
