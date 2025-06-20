@extends('layouts.tabler')

@section('content')
<header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
    <div class="container-xl px-4">
        <div class="page-header-content">
            <div class="row align-items-center justify-content-between pt-3">
                <div class="col-auto mb-3">
                    <h1 class="page-header-title">
                        <div class="page-header-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg></div>
                        Détails de l'Achat
                    </h1>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-xl px-4">
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    Informations Fournisseur
                </div>
                <div class="card-body">
                    <div class="row gx-3 mb-3">
                        <div class="col-md-6">
                            <label class="small mb-1">Nom</label>
                            <div class="form-control form-control-solid">{{ $purchase->supplier->name }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small mb-1">Email</label>
                            <div class="form-control form-control-solid">{{ $purchase->supplier->email }}</div>
                        </div>
                    </div>
                    <div class="row gx-3 mb-3">
                        <div class="col-md-6">
                            <label class="small mb-1">Téléphone</label>
                            <div class="form-control form-control-solid">{{ $purchase->supplier->phone }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small mb-1">Date de Commande</label>
                            <div class="form-control form-control-solid">{{ $purchase->purchase_date ? $purchase->purchase_date->format('d-m-Y') : 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="row gx-3 mb-3">
                        <div class="col-md-6">
                            <label class="small mb-1">No. Achat</label>
                            <div class="form-control form-control-solid">{{ $purchase->purchase_no }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small mb-1">Total</label>
                            <div class="form-control form-control-solid">{{ $purchase->total_amount }}</div>
                        </div>
                    </div>

                    <div class="row gx-3 mb-3">
                        <div class="col-md-6">
                            <label class="small mb-1">Créé par</label>
                            <div class="form-control form-control-solid">{{ $purchase->createdBy ? $purchase->createdBy->name : '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="small mb-1">Mis à jour par</label>
                            <div class="form-control form-control-solid">{{ $purchase->updatedBy ? $purchase->updatedBy->name : '-' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label  class="small mb-1">Adresse</label>
                        <div class="form-control form-control-solid">{{ $purchase->supplier->address }}</div>
                    </div>

                    @if ($purchase->purchase_status == 0)
                    <form action="{{ route('purchases.update', $purchase) }}" method="POST">
                        @csrf
                        @method('put')
                        <input type="hidden" name="id" value="{{ $purchase->id }}">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir approuver cet achat ?')">Approuver l'Achat</button>
                        <a class="btn btn-primary" href="{{ URL::previous() }}">Retour</a>
                    </form>
                    @else
                    <a class="btn btn-primary" href="{{ URL::previous() }}">Retour</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card mb-4 mb-xl-0">
                <div class="card-header">
                    Liste des Produits
                </div>

                <div class="card-body">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">No.</th>
                                        <th scope="col">Photo</th>
                                        <th scope="col">Nom du Produit</th>
                                        <th scope="col">Code Produit</th>
                                        <th scope="col">Stock Actuel</th>
                                        <th scope="col">Quantité</th>
                                        <th scope="col">Prix</th>
                                        <th scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                    <tr>
                                        <td scope="row">{{ $loop->iteration  }}</td>
                                        <td scope="row">
                                            <div style="max-height: 80px; max-width: 80px;">
                                                <img class="img-fluid"  src="{{ $product->product->product_image ? asset('storage/products/'.$product->product->product_image) : asset('assets/img/products/default.webp') }}">
                                            </div>
                                        </td>
                                        <td scope="row">{{ $product->product->product_name }}</td>
                                        <td scope="row">{{ $product->product->product_code }}</td>
                                        <td scope="row"><span class="btn btn-warning">{{ $product->product->stock }}</span></td>
                                        <td scope="row"><span class="btn btn-success">{{ $product->quantity }}</span></td>
                                        <td scope="row">{{ $product->unitcost }}</td>
                                        <td scope="row">
                                            <span  class="btn btn-primary">{{ $product->total }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection