<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Faker\Factory;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    /** @test */
    public function ユーザーはタスク一覧を取得できる(): void
    {
        $user = User::factory()->create();
        Task::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertStatus(200);
        $response->assertViewHas('tasks');
    }

    /** @test */
    public function ユーザーはタスク詳細を取得できる(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('tasks.show',$task));

        $response->assertStatus(200);
        $response->assertViewHas('task');
    }

    /** @test */
    public function ユーザーはタスク作成画面を表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.create'));

        $response->assertStatus(200);
    }


    /** @test */
    public function ユーザーはタスクを作成できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'),[
            'title' => 'テストタスク',
            'description' => 'テストの説明',
            'priority' => 2,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks',[
            'title' => 'テストタスク',
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function タスクタイトルが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'),[
            'title' => '',
            'priority' =>2,
            'category_id' => $category->id,
        ]);

        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function 無効な優先度だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => 'テストタイトル',
            'priority' => 99,
            'category_id' => $category->id,
        ]);

        $response->assertSessionHasErrors('priority');
    }

    /** @test */
    public function タイトルは255文字まで入力できる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => str_repeat('あ',255),
            'priority' => 2,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks',[
            'title' => str_repeat('あ', 255),
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function タイトルは256文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->post(route('tasks.store'), [
            'title' => str_repeat('あ',256),
            'priority' => 2,
            'category_id' => $category->id,
        ]);

        $response->assertSessionHasErrors('title');
    }

    /** @test */
    public function ユーザーはタスク編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id'=>$user->id]);

        $response = $this->actingAs($user)->get(route('tasks.edit',$task));

        $response->assertStatus(200);
        $response->assertViewHas('task');
    }

    /** @test */
    public function ユーザーはタスクを更新できる(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->put(route('tasks.update',$task),[
            'title' => '変更後のタスク名',
            'priority' => 3,
            'category_id' => $category->id,
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseHas('tasks',[
            'id' => $task->id,
            'title' => '変更後のタスク名',
            'priority' => 3,
        ]);
    }

    /** @test */
    public function ユーザーはタスクを削除できる(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete(route('tasks.destroy',$task));

        $response->assertRedirect(route('tasks.index'));
        $this->assertDatabaseMissing('tasks',['id' => $task->id]);
    }


    // --- 認可テスト ---

    /** @test */
    public function  他人のタスク詳細にアクセスすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('tasks.show', $task));

        $response->assertForbidden(); //403エラー

    }

    /** @test */
    public function  他人のタスク編集画面にアクセスすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get(route('tasks.edit',$task));

        $response->assertForbidden();

    }

    /** @test */
    public function  他人のタスクを更新しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->put(route('tasks.update',$task),[
            'title' => '不正な更新',
            'priority' => 2,
            'category_id' => $category->id,
        ]);

        $response->assertForbidden();

    }


    /** @test */
    public function  他人のタスクを削除しようとすると403エラーになる(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->delete(route('tasks.destroy',$task));

        $response->assertForbidden();

    }



}
