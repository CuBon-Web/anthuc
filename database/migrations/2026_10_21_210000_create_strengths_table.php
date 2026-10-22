<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStrengthsTable extends Migration
{
    public function up()
    {
        Schema::create('strengths', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('');
            $table->text('description')->nullable();
            $table->string('image', 500)->nullable();
            $table->string('icon', 80)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        $now = now();
        DB::table('strengths')->insert([
            [
                'title' => 'Cảm hứng từ thảo mộc Việt',
                'description' => 'Ngôn ngữ hình ảnh và sản phẩm lấy thiên nhiên làm nguồn cảm hứng.',
                'image' => 'https://images.unsplash.com/photo-1490750967868-88aa4486c946?auto=format&fit=crop&w=500&q=80',
                'icon' => 'bi bi-flower2',
                'sort' => 1,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Quy trình chỉn chu',
                'description' => 'Chú trọng sự nhất quán trong phát triển, sản xuất và hoàn thiện sản phẩm.',
                'image' => 'https://images.unsplash.com/photo-1603905179139-d6c9f7b3f1f8?auto=format&fit=crop&w=500&q=80',
                'icon' => 'bi bi-bezier2',
                'sort' => 2,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Chăm sóc trong từng khoảnh khắc',
                'description' => 'Thiết kế trải nghiệm gần gũi, dễ dùng và phù hợp nhịp sống hiện đại.',
                'image' => 'https://images.unsplash.com/photo-1556229010-6c3f2c9ca5f8?auto=format&fit=crop&w=500&q=80',
                'icon' => 'bi bi-heart',
                'sort' => 3,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('strengths');
    }
}
