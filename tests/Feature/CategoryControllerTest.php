<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Faker\Factory;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    /** @test */
    public function ユーザーはカテゴリー一覧を取得できる(): void
    {
        $user = User::factory()->create();
        Category::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
    }

    /** @test */
    public function  ユーザーはカテゴリー詳細を取得できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->get(route('categories.show',$category));

        $response->assertStatus(200);
        $response->assertViewHas('category');
    }

    /** @test */
    public function  ユーザーはカテゴリー作成画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('categories.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function  ユーザーはカテゴリーを作成できる(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->post(route('categories.store'),[
            'name' => 'テストカテゴリー',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name'=>'テストカテゴリー',
        ]);
    }

    /** @test */
    public function  カテゴリー名が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'),[
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function  カテゴリー名は255文字まで入力できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => str_repeat('あ',255),
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories',[
            'name' => str_repeat('あ',255),
        ]);
    }

    /** @test */
    public function  カテゴリー名が256文字だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => str_repeat('あ', 256),
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function  ユーザーはカテゴリー編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->get(route('categories.edit',$category));

        $response->assertStatus(200);
        $response->assertViewHas('category');
    }

    /** @test */
    public function  ユーザーはカテゴリーを更新できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->put(route('categories.update',$category),[
            'name' => '更新後のカテゴリー名',
        ]);

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories',[
            'name' => '更新後のカテゴリー名',
        ]);
    }

    /** @test */
    public function  ユーザーはカテゴリーを削除できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->delete(route('categories.destroy',$category));

        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories',['id'=> $category->id]);
    }

    /** @test */
    public function  タスクが紐づいているカテゴリーは削除できない(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories',['id' => $category->id]);
    }
}
