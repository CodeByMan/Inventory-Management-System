<div class="grid two">
<label>SKU<input name="sku" value="{{ old('sku', $product->sku ?? '') }}" maxlength="100"></label>
<label>Name<input name="name" value="{{ old('name', $product->name ?? '') }}" required maxlength="255"></label>
<label>Price<input type="number" name="price" value="{{ old('price', $product->price ?? '0.00') }}" min="0" step="0.01" required></label>
<label>Minimum quantity<input type="number" name="minimum_quantity" value="{{ old('minimum_quantity', $product->minimum_quantity ?? 0) }}" min="0" required></label>
@if(!isset($product))<label>Opening stock<input type="number" name="opening_stock" value="{{ old('opening_stock',0) }}" min="0"><small>Creates an opening stock movement.</small></label>@endif
<label>Category<input name="category" value="{{ old('category', $product->category ?? '') }}" maxlength="120"></label>
<label>Supplier<select name="supplier_id"><option value="">No supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id',$product->supplier_id ?? '')==$supplier->id)>{{ $supplier->name }}</option>@endforeach</select></label>
</div>
<label>Description<textarea name="description" rows="5" maxlength="5000">{{ old('description', $product->description ?? '') }}</textarea></label>