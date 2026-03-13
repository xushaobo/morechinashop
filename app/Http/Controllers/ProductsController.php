<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;

use App\Models\Product;
use App\Exceptions\InvalidRequestException;

use App\Services\CategoryService;

class ProductsController extends Controller
{
public function index(Request $request, CategoryService $categoryService)
{
    $builder = Product::query()->where('on_sale', true);

    $search = $request->input('search', '');
    $order = $request->input('order', ''); // 你已经在后面用到了 $order

    if ($search) {
        $like = '%' . $search . '%';
        // 筛选出至少有一个SKU标题或描述匹配的商品
        $builder->where(function ($query) use ($like) {
            $query->orWhereHas('skus', function ($query) use ($like) {
                $query->where(function ($q) use ($like) {
                    $q->where('title', 'like', $like)
                      ->orWhere('description', 'like', $like);
                });
            });
        });

        // 预加载时只加载匹配搜索条件的SKU
        $builder->with(['skus' => function ($query) use ($like) {
            $query->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('description', 'like', $like);
            });
        }]);
    } else {
        // 无搜索时，加载所有SKU
        $builder->with('skus');
    }

    // 类目筛选（保持不变）
    if ($request->input('category_id') && $category = Category::find($request->input('category_id'))) {
        if ($category->is_directory) {
            $builder->whereHas('category', function ($query) use ($category) {
                $query->where('path', 'like', $category->path . $category->id . '-%');
            });
        } else {
            $builder->where('category_id', $category->id);
        }
    }

    // 排序（保持不变）
    if ($order) {
        if (preg_match('/^(.+)_(asc|desc)$/', $order, $m)) {
            if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                $builder->orderBy($m[1], $m[2]);
            }
        }
    }

    // 分页
    $products = $builder->paginate(16)->appends(request()->query());

    // 为每个商品添加库存标志
    $products->each(function ($product) use ($search) {
        // 整体是否有货（所有SKU中是否有库存>0）
        $product->has_stock = $product->skus->contains(function ($sku) {
            return $sku->stock > 0;
        });

        // 匹配的SKU中是否有货（如果无搜索，则与整体相同）
        if ($search) {
            $product->matching_skus_have_stock = $product->skus->contains(function ($sku) {
                return $sku->stock > 0;
            });
        } else {
            $product->matching_skus_have_stock = $product->has_stock;
        }
    });

    return view('products.index', [
        'products' => $products,
        'filters'  => [
            'search' => $search,
            'order'  => $order,
        ],
        'category' => $category ?? null,
        'categoryTree' => $categoryService->getCategoryTree(),
    ]);
}
public function show(Product $product, Request $request)
{
    if (!$product->on_sale) {
        throw new InvalidRequestException('商品未上架');
    }

    // 加载 SKU 关联（如果模型没有默认加载的话）
    // 如果已经在模型全局作用域或控制器其他位置加载过，可省略此行
    $product->load('skus');

    $favored = false;
    if ($user = $request->user()) {
        $favored = boolval($user->favoriteProducts()->find($product->id));
    }

    return view('products.show', [
        'product'     => $product,
        'favored'     => $favored,
    ]);
    
    
}

  public function favor(Product $product, Request $request)
    {
        $user = $request->user();
        if ($user->favoriteProducts()->find($product->id)) {
            return [];
        }

        $user->favoriteProducts()->attach($product);

        return [];
    }

	 public function disfavor(Product $product, Request $request)
    {
        $user = $request->user();
        $user->favoriteProducts()->detach($product);

        return [];
    }

	 public function favorites(Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', ['products' => $products]);
    }
}
