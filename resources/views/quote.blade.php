@extends('layouts.app')

@section('content')
        <div class="container">
            <form class="form-inline service_level needs-validation" novalidate action="" method="post" id="quoting-form">
                {{ csrf_field() }}
                <input type="hidden" id="num-product" name="num-product" value="{{ old('num-product') ?? 1 }}" />
                <section class="jumbotron">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">Order Number <span class="ml-1 red">(*)</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control required number" name="order_number" id="ip-order">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="title-page">Where are we picking up your shipment?</h3>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">Pickup Date <span class="ml-1 red">(*)</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control required" name="pickup_date" id="pickup_date">
                                </div>
                            </div>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">Pick From</label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control" value="Zuri" readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Address Type </label>
                                <div class="col-sm-8">
                                    <input type="radio" name="pickup_type" value="BUSINESS" checked disabled> Business <br/>
                                    <input type="radio" name="pickup_type" value="RESIDENCE" disabled> Residence
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h3 class="title-page">Where is the shipment going?</h3>
                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">Zip Code <span class="ml-1 red">(*)</span></label>
                                <div class="col-sm-8">
                                    <input type="text" class="form-control required number" name="shipping_zip_code">
                                </div>
                            </div>

                            <div class="form-group row mb-2">
                                <label class="col-sm-4 col-form-label">Address Type <span class="ml-1 red">(*)</span></label>
                                <div class="col-sm-8">
                                    <div class="form-check">
                                        <input checked class="form-check-input" type="radio" name="shipping_address_type" id="{{ADR_TYPE_BUSINESS}}" value="{{ADR_TYPE_BUSINESS}}">
                                        <label class="form-check-label" for="{{ADR_TYPE_BUSINESS}}">Business</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="shipping_address_type" id="{{ADR_TYPE_BUSINESS}}" value="{{ADR_TYPE_RESIDENTIAL}}">
                                        <label class="form-check-label" for="{{ADR_TYPE_BUSINESS}}">Residential</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-4 col-form-label">Shipping Method<span class="ml-1 red">(*)</span></label>
                                <div class="col-sm-8">
                                    <select id="sl-service" class="form-control" name="service_level_array[]" multiple>
                                        <option value="{{SERVICE_LEVEL_DOCK_TO_DOCK}}" selected>{{SERVICE_LEVEL[SERVICE_LEVEL_DOCK_TO_DOCK]}}</option>
                                        <option value="{{SERVICE_LEVEL_CURBSIDE}}">{{SERVICE_LEVEL[SERVICE_LEVEL_CURBSIDE]}}</option>
                                        <option value="{{SERVICE_LEVEL_ROOM_OF_CHOOSE}}">{{SERVICE_LEVEL[SERVICE_LEVEL_ROOM_OF_CHOOSE]}}</option>
                                        <option value="{{SERVICE_LEVEL_WHITE_GLOVE}}">{{SERVICE_LEVEL[SERVICE_LEVEL_WHITE_GLOVE]}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mt-5">
                            <h3 class="title-page">What are you shipping?</h3>
                            <div class="row justify-content-md-center">
                                <div class="col-sm-12 col-md-10">
                                    <div id="product-lists">
                                    </div>
                                    <div class="text-right">
                                        <button type="button" class="bt-add btn btn-secondary btn-sm"><i class="fas fa-plus-circle"></i> Add Record</button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right mt-4 mr-4">
                                <button type="submit" class="btn btn-primary mt-2" id="bt-submit"><i class="fas fa-search"></i> Get quote</button>
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </div>


        <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="result-modal" id="result-modal" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Order number: #<span id="order-number"></span></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div id="mModal"></div>
                </div>
            </div>
        </div>
@endsection
