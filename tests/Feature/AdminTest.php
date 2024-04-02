<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;
    // 관리자 회원가입 테스트
    public function test_admin_register(): void
    {
        $userData = [
            'name' => fake()->name,
            'phone_number' => fake()->phoneNumber,
            'email' => fake()->email,
            'password' => fake()->password,
        ];
        $response = $this->post(route('admin.register'), $userData);
        $response->assertStatus(201);
    }
    // 관리자 회원탈퇴 테스트
    public function test_admin_unregister(): void
    {
        $userID = random_int(1, 50);
        $response = $this->delete(route('admin.unregister', ['id' => $userID]));
        $response->assertStatus(200);
    }
    // 관리자 로그인 테스트
    public function test_admin_login(): void
    {
        $session = $this->get('sanctum/csrf-cookie');

        $csrfCookie = $session->getCookie('XSRF-TOKEN')->getValue();

        $fakePassword = fake()->password(10);

        $admin = Admin::factory()->create([
            'password' => $fakePassword,
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => $fakePassword, // 실제 비밀번호
        ], ['XSRF-TOKEN' => $csrfCookie]);

        $response->assertStatus(200);

        $this->assertAuthenticated();
    }
    // 관리자 로그아웃 테스트
    public function test_admin_logout():void
    {
        Sanctum::actingAs(
            Admin::factory()->create()
        );
        $response = $this->post(route('admin.logout'));

        $response->assertStatus(200);
    }
    // 관리자 권한 변경 테스트
    public function test_admin_privilege():void
    {
        $admin = Sanctum::actingAs(
            Admin::factory()->create()
        );
        $response = $this->patch(route('admin.privilege'), [
            'id' => $admin->id,
            'salon_privilege' => fake()->boolean,
            'admin_privilege' => fake()->boolean,
            'restaurant_privilege' => fake()->boolean,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'update privilege successfully']);
    }
    // 관리자 승인 테스트
    public function test_admin_approve():void
    {
        $admin = Sanctum::actingAs(
            Admin::factory()->create()
        );

        $response = $this->patch(route('admin.approve'), [
            'admin_id' => $admin->id,
            'approve' => fake()->boolean,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'update approve status successfully']);
    }
    // 관리자 정보 업데이트 테스트
    public function test_admin_update():void
    {
        $admin = Sanctum::actingAs(
            Admin::factory()->create()
        );

        $response = $this->patch(route('admin.update'), [
            'admin_id' => $admin->id,
            'name' => fake()->name,
            'phone_number' => fake()->phoneNumber,
            'password' => fake()->password,
        ]);

        $response->assertStatus(200);

        $response->assertJson(['message' => 'Update profile successfully']);
    }
    // 이메일 중복 체크 테스트
    public function test_admin_verify_email():void
    {
        $admin = Sanctum::actingAs(
          Admin::factory()->create()
        );

        $response = $this->get(route('admin.verify.email', ['email' => $admin->email]));

        $response->assertStatus(200);
    }
    // 비밀번호 확인 테스트
    public function test_admin_verify_password():void
    {
        $fakePassword = fake()->password(10);
        $admin = Sanctum::actingAs(
            Admin::factory()->create([
                'password' => $fakePassword,
            ])
        );

        $response = $this->post(route('admin.verify.pw'), [
            'password' => $fakePassword,
        ]);

        $response->assertStatus(200);
    }

    public function test_admin_find_email():void
    {
        $admin = Admin::factory()->create();

        $response = $this->post(route('admin.find.email'), [
            'name' => $admin->name,
            'phone_number' => $admin->phone_number
        ]);

        $response->assertStatus(200);
    }
}
