@extends('store.layout.app')

@section ('content')
<div class="container-fluid">
          <div class="row">
             <div class="col-lg-12">
                @if (session()->has('success'))
               <div class="alert alert-success">
                @if(is_array(session()->get('success')))
                        <ul>
                            @foreach (session()->get('success') as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                        @else
                            {{ session()->get('success') }}
                        @endif
                    </div>
                @endif
                 @if (count($errors) > 0)
                  @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                      {{$errors->first()}}
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                  @endif
                @endif
                </div> 
            <div class="col-md-12">
              <div class="card">
                <div class="card-header card-header-primary">
                  <h4 class="card-title">{{ __('keywords.Add Coupon')}}</h4></div>
                  <form class="forms-sample" action="{{route('addcoupon')}}" method="post" enctype="multipart/form-data">
                      {{csrf_field()}}
                    <div class="card-body">
                      <form>
                        <div class="row g-3">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="coupon_type" class="bmd-label-floating">{{ __('keywords.Coupon_Type')}}</label>
                              <select class="form-control form-control-sm img" name="coupon_type" id="coupon_type">
                                <option value="regular">{{ __('keywords.Reg_Coupon')}}</option>
                                <option value="firstsale">{{ __('keywords.Fst_Coupon')}}</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="exampleFormControlSelect3">{{ __('keywords.Uses Restriction')}}</label>
                              <input type="number" name="restriction" class="form-control" placeholder="{{ __('keywords.coupon_limuses_holder')}}"  min="1" required>
                            </div>
                          </div>
                        </div>
                        <div class="row align-items-end">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="bmd-label-floating">{{ __('keywords.Coupon Name')}}</label>
                              <input type="text" name="coupon_name" class="form-control" value="" placeholder="{{ __('keywords.coupon_name_holder')}}" required>
                            </div>
                          </div>
                          <div class="col-md-5">
                            <div class="form-group">
                              <label class="bmd-label-floating">{{ __('keywords.Coupon Code')}}</label>
                              <input type="text" id="coupon_code" name="coupon_code" minlength="5" maxlength="11" class="form-control" placeholder="{{ __('keywords.coupon_code_holder')}}" required>
                            </div>
                          </div>
                          <div class="col-md-1 mb-1">
                            <div class="form-group">
                              <a href="#" id="genCouponCode">{{ __('keywords.GenCouponCode')}}</a>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="bmd-label-floating">{{ __('keywords.Description')}}</label>
                              <input type="text" name="coupon_desc" class="form-control" placeholder="{{ __('keywords.coupon_desc_holder')}}">
                            </div>
                          </div>
                          <div class="col-md-6">
                            <label class="bmd-label-floating">{{ __('keywords.Coupon')}} {{ __('keywords.Image')}}<b>({{ __('keywords.It Should Be Less Then 1000 KB')}})</b></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="customFile" name="image" accept="image/*" required/>
                                <label class="custom-file-label" for="customFile">{{__('keywords.Choose_File')}}</label>
                              </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <p class="card-description">{{ __('keywords.From Date')}}</p>
                              <input type="datetime-local" name="valid_from" class="form-control" value="{{old('valid_to')}}">
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <p class="card-description">{{ __('keywords.To Date')}}</p>
                              <input type="datetime-local" name="valid_to" class="form-control" value="{{old('valid_from')}}">
                            </div>
                          </div>
                        </div>
                        <div class="dropdown-divider"></div><br>
                        <div class="row"> 
                          <div class="col-md-2">
                            <div class="form-group">
                              <label for="discount_type">{{ __('keywords.Discount')}}</label>
                              <select class="form-control img" id="discount_type" name="discount_type" required>
                                <option values="">{{ __('keywords.Select')}}</option>
                                <option value="percent">{{ __('keywords.Percentage')}}</option>
                                <option value="amount">{{ __('keywords.Price')}}</option>
                              </select>
                            </div>
                          </div>
                          <div id="coupon_discount" class="col-md-4 align-self-center mt-md-3">
                            <input type="number" class="form-control col-auto des_price" id="coupon_discountxt" name="coupon_discountxt" placeholder="{{ __('keywords.coupon_discper_holder')}}" value="{{old('coupon_discount')}}" required>
                          </div>
                          <div class="col-md-4 offset-md-1">
                            <div class="form-group">
                              <p class="card-description">{{ __('keywords.Minimum Cart Value')}}</p>
                              <input type="number" name="cart_value" class="form-control mt-md-2" value="" min="1" placeholder="{{ __('keywords.coupon_mincart_holder')}}">
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div id="max_discount" class="col-md-4">
                            <div class="form-group">
                              <p class="card-description">{{ __('keywords.Max_Discount')}}</p>
                              <input type="number" name="max_discountxt" id="max_discountxt" class="form-control" value="{{old('max_discount')}}" min="0" placeholder="{{ __('keywords.coupon_discmax_holder')}}">
                            </div>
                          </div>
                        </div>
                      <button type="submit" class="btn btn-primary pull-center">{{ __('keywords.Submit')}}</button>
                      <a href="{{route('couponlist')}}" class="btn btn-secondary">{{ __('keywords.Cancel')}}</a>
                      <div class="clearfix"></div>
                </form>
              </div>
            </div>
          </div>
			  </div>
      </div> 
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>    {{-- // TODO: Check if jQuery is loaded already or need to update reference --}}
<script type="text/javascript">
  $(document).ready(function(){
    $("#coupon_discount").hide();
    $("#max_discount").hide();
    
    $("#discount_type").on('change', function(){
      var discount_type = $("#discount_type option:selected").val();
      if(discount_type=='percent'||discount_type=='amount'){
        $("#coupon_discount").show();
        if(discount_type!='percent'){
          $("#coupon_discountxt").attr('placeholder', '{{ __('keywords.coupon_discam_holder')}}');
          $("#coupon_discountxt").attr('min', '0.05');
          $("#coupon_discountxt").attr('step', '0.01');
          $("#coupon_discountxt").attr('max', '');
          $("#max_discount").hide();
        } else {
          $("#coupon_discountxt").attr('placeholder', '{{ __('keywords.coupon_discper_holder')}}');
          $("#coupon_discountxt").attr('min', '1');
          $("#coupon_discountxt").attr('max', '99');
          $("#max_discount").show();
        }
      } else {
        $("#coupon_discount").hide();
        $("#max_discount").hide();
      }
    });    

    $("#genCouponCode").click(function (e) { 
      e.preventDefault();
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      var xhrObjCall= $.ajax({
          async: true,
          global: false,
          type: "POST",
          url: "/api/genCouponCode",
          dataType: "text",
          error: function(xhrObj, response){
            window.alert('There was an error while generating code. Try again later.');
          },
          success: function (response) {
            $('#coupon_code').val(response);
          }
        });
    });
  });
</script>

  @endsection
