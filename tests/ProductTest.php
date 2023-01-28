<?php

namespace App\Tests;

use App\Models\Product;
use Artel\Support\Casts\PostgresArray;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ProductTest extends TestCase
{
    protected $admin;
    protected $productOwner;
    protected $user;

    public function setUp() : void
    {
        parent::setUp();

        $this->admin = User::find(1);
        $this->productOwner = User::find(2);
        $this->user = User::find(3);
    }

    public function testCreate()
    {
        $data = $this->getJsonFixture('create_product_request.json');

        $response = $this->actingAs($this->user)->json('post', '/products', $data);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEqualsFixture('create_product_response.json', $response->json());
    }

    public function testCreateCheckDB()
    {
        $data = $this->getJsonFixture('create_product_request.json');

        $response = $this->actingAs($this->user)->json('post', '/products', $data);

        $product = Product::find($response->json('id'));

        $expected = $this->getJsonFixture('create_product_response.json');
        Arr::forget($expected, 'media');
        $expected['tags'] = app(PostgresArray::class)->set($product, 'tags', $expected['tags'], []);

        $this->assertDatabaseHas('products', $expected);
    }

    public function testCreateCheckMedia()
    {
        $data = $this->getJsonFixture('create_product_request.json');

        $response = $this->actingAs($this->user)->json('post', '/products', $data);

        foreach ($data['media'] as $mediaId) {
            $this->assertDatabaseHas('media_product', [
                'media_id' => $mediaId,
                'product_id' => $response->json('id')
            ]);
        }
    }

    public function testCreateNoAuth()
    {
        $data = $this->getJsonFixture('create_product_request.json');

        $response = $this->json('post', '/products', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testUpdate()
    {
        $data = $this->getJsonFixture('update_product_request.json');

        $response = $this->actingAs($this->productOwner)->json('put', '/products/2', $data);

        $response->assertOk();
    }

    public function testUpdateCheckDB()
    {
        $data = $this->getJsonFixture('update_product_request.json');

        $this->actingAs($this->productOwner)->json('put', '/products/2', $data);

        $data['id'] = 2;
        Arr::forget($data, 'media');
        $data['tags'] = app(PostgresArray::class)->set(
            Product::find(2),
            'tags',
            explode(',', $data['tags']),
            []
        );

        $this->assertDatabaseHas('products', $data);
    }

    public function testUpdateCheckMedia()
    {
        $data = $this->getJsonFixture('update_product_request.json');

        $this->actingAs($this->admin)->json('put', '/products/1', $data);

        $this->assertDatabaseHas('media_product', [ 'media_id' => 1, 'product_id' => 1 ]);
        $this->assertDatabaseMissing('media_product', [ 'media_id' => 2, 'product_id' => 1 ]);
        $this->assertDatabaseHas('media_product', [ 'media_id' => 3, 'product_id' => 1 ]);
    }

    public function testUpdateTryToChangeStatus()
    {
        $response = $this->actingAs($this->productOwner)->json('put', '/products/2', [
            'status' => Product::APPROVED_STATUS
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateWithoutPermission()
    {
        $data = $this->getJsonFixture('update_product_request.json');

        $response = $this->actingAs($this->user)->json('put', '/products/1', $data);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testUpdateAsAdmin()
    {
        $data = $this->getJsonFixture('update_product_request.json');

        $response = $this->actingAs($this->admin)->json('put', '/products/2', $data);

        $response->assertOk();
    }

    public function testUpdateTryToChangeStatusAsAdmin()
    {
        $response = $this->actingAs($this->admin)->json('put', '/products/2', [
            'status' => Product::APPROVED_STATUS
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('products', [ 'status' => Product::APPROVED_STATUS ]);
    }

    public function testUpdateNotExists()
    {
        $data = $this->getJsonFixture('update_product_request.json');

        $response = $this->actingAs($this->admin)->json('put', '/products/0', $data);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testUpdateNoAuth()
    {
        $data = $this->getJsonFixture('update_product_request.json');

        $response = $this->json('put', '/products/1', $data);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testDelete()
    {
        $response = $this->actingAs($this->admin)->json('delete', '/products/1');

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('products', [ 'id' => 1 ]);
    }

    public function testDeleteForce()
    {
        $response = $this->actingAs($this->admin)->json('delete', '/products/1', [ 'force' => 1 ]);

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('products', [ 'id' => 1 ]);
    }

    public function testDeleteAsTheOwner()
    {
        $response = $this->actingAs($this->productOwner)->json('delete', '/products/2');

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertSoftDeleted('products', [ 'id' => 2 ]);
    }

    public function testForceDeleteAsTheOwner()
    {
        $response = $this->actingAs($this->productOwner)->json('delete', '/products/2', [
            'force' => 1
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteWithoutPermission()
    {
        $response = $this->actingAs($this->user)->json('delete', '/products/1');

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function testDeleteNotExists()
    {
        $response = $this->actingAs($this->admin)->json('delete', '/products/0');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testDeleteNoAuth()
    {
        $response = $this->json('delete', '/products/1');

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function testGetApprovedAsAdmin()
    {
        $response = $this->actingAs($this->user)->json('get', '/products/1');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture('get_approved_product.json', $response->json());
    }

    public function testGetApprovedAsUser()
    {
        $response = $this->actingAs($this->user)->json('get', '/products/1');

        $response->assertOk();

        $this->assertEqualsFixture('get_approved_product.json', $response->json());
    }

    public function testGetApprovedAsGuest()
    {
        $response = $this->json('get', '/products/1');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture('get_approved_product.json', $response->json());
    }

    public function testGetRejectedAsAdmin()
    {
        $response = $this->actingAs($this->productOwner)->json('get', '/products/2');

        $response->assertOk();

        $this->assertEqualsFixture('get_rejected_product.json', $response->json());
    }

    public function testGetRejectedAsUser()
    {
        $response = $this->actingAs($this->user)->json('get', '/products/2');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGetRejectedAsOwner()
    {
        $response = $this->actingAs($this->productOwner)->json('get', '/products/2');

        $response->assertOk();

        $this->assertEqualsFixture('get_rejected_product.json', $response->json());
    }

    public function testGetRejectedAsGuest()
    {
        $response = $this->json('get', '/products/2');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGetPendingAsUser()
    {
        $response = $this->actingAs($this->user)->json('get', '/products/6');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGetPendingAsAdmin()
    {
        $response = $this->actingAs($this->admin)->json('get', '/products/6');

        $response->assertOk();

        $this->assertEqualsFixture('get_pending_product.json', $response->json());
    }

    public function testGetPendingAsOwner()
    {
        $response = $this->actingAs($this->productOwner)->json('get', '/products/6');

        $response->assertOk();

        $this->assertEqualsFixture('get_pending_product.json', $response->json());
    }

    public function testGetPendingAsGuest()
    {
        $response = $this->json('get', '/products/6');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testGetNotExists()
    {
        $response = $this->actingAs($this->admin)->json('get', '/products/0');

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function getSearchFilters()
    {
        return [
            [
                'filter' => ['all' => 1],
                'result' => 'search_all.json'
            ],
            [
                'filter' => [
                    'page' => 2,
                    'per_page' => 2
                ],
                'result' => 'search_by_page_per_page.json'
            ],
            [
                'filter' => [
                    'query' => 'first'
                ],
                'result' => 'search_by_query.json'
            ],
            [
                'filter' => [
                    'order_by' => 'created_at',
                    'desc' => 1
                ],
                'result' => 'search_order_by_created_at.json'
            ],
            [
                'filter' => [
                    'order_by' => 'random'
                ],
                'result' => 'search_order_by_random.json'
            ],
            [
                'filter' => [
                    'user_id' => 3
                ],
                'result' => 'search_by_user.json'
            ],
        ];
    }

    /**
     * @dataProvider getSearchFilters
     *
     * @param array $filter
     * @param string $fixture
     */
    public function testSearch($filter, $fixture)
    {
        $response = $this->json('get', '/products', $filter);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }

    /**
     * @dataProvider getSearchFilters
     *
     * @param array $filter
     * @param string $fixture
     */
    public function testSearchAsUser($filter, $fixture)
    {
        $fixture = Str::replace('.json', '_as_user.json', $fixture);

        $response = $this->actingAs($this->admin)->json('get', '/products', $filter);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }

    /**
     * @dataProvider getSearchFilters
     *
     * @param array $filter
     * @param string $fixture
     */
    public function testSearchAsGuest($filter, $fixture)
    {
        $fixture = Str::replace('.json', '_as_guest.json', $fixture);

        $response = $this->json('get', '/products', $filter);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEqualsFixture($fixture, $response->json());
    }
}
