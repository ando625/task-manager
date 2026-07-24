<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnauthenticatedRedirectTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */

    /** @test */
    public function 未認証ユーザーはタスク一覧にアクセスするとログインページにリダイレクトされる(): void
    {
        $response = $this->get(route('tasks.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはタスク作成画面にアクセスするとログインページにリダイレクトされる(): void
    {
        $response = $this->get(route('tasks.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはカテゴリー一覧にアクセスするとログインページにリダイレクトされる(): void
    {
        $response = $this->get(route('categories.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはカテゴリー作成画面にアクセスするとログインページにリダイレクトされる(): void
    {
        $response = $this->get(route('categories.create'));

        $response->assertRedirect(route('login'));
    }

    
}
