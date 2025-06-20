@extends('layouts.tabler')

@section('content')
    <div class="page-body">
        <div class="container-xl">
            <x-alert/>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ __('Edit Quotation') }}
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('quotations.update', $quotation) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="reference" class="form-label">{{ __('Quotation Number') }}</label>
                            <input type="text" name="reference" id="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference', $quotation->reference) }}" required>
                            @error('reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="date" class="form-label">{{ __('Date') }}</label>
                            <input type="date" name="date" id="date" class="form-control @error('date') is-invalid @enderror" value="{{ old('date', $quotation->date->format('Y-m-d')) }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customer_id" class="form-label">{{ __('Customer Name') }}</label>
                            <select name="customer_id" id="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select a customer') }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $quotation->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- You would typically have a section here to edit the products in the quotation.
                             This is more complex and usually involves Livewire or JavaScript to dynamically add/remove/update products.
                             For simplicity in this example, we are omitting the detailed product editing part,
                             as the QuotationController's `update` method currently only updates the main quotation fields.
                             If you uncommented the 'TODO' section in your QuotationController's `update` method,
                             you would need to build out this UI.
                        --}}

                        <div class="mb-3">
                            <label for="tax_percentage" class="form-label">{{ __('Tax Percentage') }}</label>
                            <input type="number" name="tax_percentage" id="tax_percentage" class="form-control @error('tax_percentage') is-invalid @enderror" value="{{ old('tax_percentage', $quotation->tax_percentage) }}" required>
                            @error('tax_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="discount_percentage" class="form-label">{{ __('Discount Percentage') }}</label>
                            <input type="number" name="discount_percentage" id="discount_percentage" class="form-control @error('discount_percentage') is-invalid @enderror" value="{{ old('discount_percentage', $quotation->discount_percentage) }}" required>
                            @error('discount_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shipping_amount" class="form-label">{{ __('Shipping Amount') }}</label>
                            <input type="number" name="shipping_amount" id="shipping_amount" class="form-control @error('shipping_amount') is-invalid @enderror" value="{{ old('shipping_amount', $quotation->shipping_amount) }}" step="0.01" required>
                            @error('shipping_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="total_amount" class="form-label">{{ __('Total Amount') }}</label>
                            <input type="number" name="total_amount" id="total_amount" class="form-control @error('total_amount') is-invalid @enderror" value="{{ old('total_amount', $quotation->total_amount) }}" step="0.01" required>
                            @error('total_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">{{ __('Status') }}</label>
                            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                {{-- Assuming you have the QuotationStatus Enum uncommented in your controller and it provides labels.
                                     Otherwise, you'd list static options here. --}}
                                @foreach(\App\Enums\QuotationStatus::cases() as $status)
                                    <option value="{{ $status->value }}" {{ old('status', $quotation->status->value) == $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">{{ __('Note') }}</label>
                            <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" rows="3">{{ old('note', $quotation->note) }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary">{{ __('Update Quotation') }}</button>
                            <a href="{{ route('quotations.index') }}" class="btn btn-secondary">{{ __('Cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection