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
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained()->onDelete('cascade');
            $table->foreignId(column: 'marque_id')->constrained()->onDelete('cascade');

            $table->string('modele');
            $table->string('immatriculation')->unique();
            $table->integer('annee');
            $table->string('couleur');
            $table->enum('boite_vitesse', ['manuelle', 'automatique']);
            $table->enum('type_carburant', ['essence', 'diesel', 'hybride', 'electrique']);
            $table->integer('nombre_places');
            $table->boolean('climatisation')->default(true);
            $table->boolean('gps')->default(false);
            $table->integer('kilometrage');

            $table->decimal('prix_journalier', 10, 2);
            $table->text('description')->nullable();
            $table->enum('disponibilite', ['disponible', 'reserve', 'en_maintenance'])->default('disponible');

            $table->json('images')->nullable();
            $table->string('video')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicules');
    }
};
