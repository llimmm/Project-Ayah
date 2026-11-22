<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasar', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('kecamatan');
            $t->string('alamat')->nullable();
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->string('role', 20)->default('enumerator')->after('email'); // admin | verifikator | enumerator
            $t->foreignId('pasar_id')->nullable()->after('role')->constrained('pasar')->nullOnDelete();
            $t->boolean('aktif')->default(true)->after('pasar_id');
        });

        Schema::create('komoditas', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('kategori');
            $t->string('satuan', 30);
            $t->unsignedInteger('harga_acuan')->nullable(); // HET / HAP (Rp)
            $t->boolean('masuk_iph')->default(false);
            $t->unsignedSmallInteger('urutan')->default(0);
            $t->boolean('aktif')->default(true);
            $t->timestamps();
        });

        Schema::create('harga_harian', function (Blueprint $t) {
            $t->id();
            $t->date('tanggal');
            $t->foreignId('pasar_id')->constrained('pasar')->cascadeOnDelete();
            $t->foreignId('komoditas_id')->constrained('komoditas')->cascadeOnDelete();
            $t->unsignedInteger('harga');
            $t->string('status', 12)->default('draft'); // draft | diajukan | disetujui | ditolak
            $t->string('catatan')->nullable();
            $t->foreignId('diinput_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('diverifikasi_pada')->nullable();
            $t->timestamps();
            $t->unique(['tanggal', 'pasar_id', 'komoditas_id']);
            $t->index(['status', 'tanggal']);
        });

        Schema::create('iph_mingguan', function (Blueprint $t) {
            $t->id();
            $t->unsignedSmallInteger('tahun');
            $t->unsignedTinyInteger('bulan');
            $t->unsignedTinyInteger('minggu_ke');
            $t->decimal('perubahan_iph', 6, 2); // persen
            $t->string('komoditas_andil')->nullable(); // dipisah koma, urut andil terbesar
            $t->string('sumber')->default('BPS / Kemendagri');
            $t->timestamps();
            $t->unique(['tahun', 'bulan', 'minggu_ke']);
        });

        Schema::create('inflasi', function (Blueprint $t) {
            $t->id();
            $t->string('wilayah'); // Provinsi NTB, Kota Mataram, Kota Bima, Kab. Sumbawa
            $t->unsignedSmallInteger('tahun');
            $t->unsignedTinyInteger('bulan');
            $t->decimal('ihk', 8, 2)->nullable();
            $t->decimal('mtm', 6, 2)->nullable();
            $t->decimal('ytd', 6, 2)->nullable();
            $t->decimal('yoy', 6, 2)->nullable();
            $t->timestamps();
            $t->unique(['wilayah', 'tahun', 'bulan']);
        });

        Schema::create('berita', function (Blueprint $t) {
            $t->id();
            $t->string('judul');
            $t->string('slug')->unique();
            $t->string('kategori', 20)->default('berita'); // berita | kegiatan | dokumen
            $t->text('ringkasan')->nullable();
            $t->longText('isi')->nullable();
            $t->string('gambar')->nullable();
            $t->string('lampiran')->nullable();
            $t->timestamp('terbit_pada')->nullable();
            $t->foreignId('penulis_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berita');
        Schema::dropIfExists('inflasi');
        Schema::dropIfExists('iph_mingguan');
        Schema::dropIfExists('harga_harian');
        Schema::dropIfExists('komoditas');
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('pasar_id');
            $t->dropColumn(['role', 'aktif']);
        });
        Schema::dropIfExists('pasar');
    }
};
