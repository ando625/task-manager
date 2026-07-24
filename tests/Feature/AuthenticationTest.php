<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    /** @test */
    public function ログイン画面を表示できる(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    /** @test */
    public function 正しい認証情報でログインできる(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'),[
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('tasks.index'));
        $this->assertAuthenticatedAs($user); //認証されてるユーザーかどうか

    }

    /** @test */
    public function 間違ったパスワードではログインできない(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password1234')
        ]);

        $response = $this->post(route('login'),[
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest(); //ゲスト、認証されていないかどうか
    }

    /** @test */
    public function 存在しないメールアドレスではログインできない(): void
    {
        $response = $this->post(route('login'),[
            'email' => 'nonexistent@example.com',
            'password' => 'password1234'
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function メールアドレスが空だとバリデーションエラーになる(): void
    {
        $response = $this->post(route('login'),[
            'email' => '',
            'password' => 'password1234',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワードが空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function ログアウトできる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function 認証済みユーザーはログインページにアクセスするとリダイレクトされる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('tasks.index'));
    }
}
