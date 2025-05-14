<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicule_id')->constrained()->onDelete('cascade'); 
            $table->enum('location_type',['externe','interne'])->default('externe');
            // Pour les réservations internes (clients non enregistrés)
            $table->string('client_nom')->nullable();
            $table->string('client_telephone')->nullable();
            $table->string('client_email')->nullable();
            $table->text('client_adresse')->nullable();
            
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('statut', ['en_attente', 'confirmée','refuser', 'annulée', 'terminée'])->default('en_attente');
            $table->timestamps();
          });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
