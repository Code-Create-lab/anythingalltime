<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8" />
	<title>{{$logo->name}} Admin</title>
	<link rel="apple-touch-icon" sizes="76x76" href="{{url('assets/img/apple-icon.png')}}">
    @if ($url_aws ?? "/")
        <link rel="icon" type="image/png" href="{{url($logo->favicon)}}">
    @else
        <link rel="icon" type="image/png" href="{{$url_aws.$logo->favicon}}">
    @endif
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="Anything Alltime Multistore" />
    <link href="{{url('assets/theme_assets/css/app.min.css')}}" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-icons/3.0.2/iconfont/material-icons.min.css" integrity="sha512-y9glprRcVESvYY3suAqBUYXx0ySbQNvbzzgvLy9F2o38Y7XCNeq/No2gnHjV3+Rjyq5ijoPZRMXotpdw6jcG4g==" crossorigin="anonymous" />
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300;400;500;600;700&family=Fira+Code:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{url('assets/theme_assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}" rel="stylesheet" />
    <link href="{{url('assets/theme_assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css')}}" rel="stylesheet" />
    <link href="{{url('assets/theme_assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css')}}" rel="stylesheet" />
    <link href="{{url('assets/theme_assets/plugins/bootstrap-table/dist/bootstrap-table.min.css')}}" rel="stylesheet" />
    @yield('preload-section')
    <link href="{{url('assets/theme_assets/css/admin-pro.css')}}" rel="stylesheet" /> {{-- GoGrocer Pro theme: must stay last to override every stylesheet above --}}
      @livewireStyles
      @powerGridStyles
  </head>
<body data-spy='scroll' data-target='#sidebar-bootstrap' data-offset='200'>
  	<div id="app" class="app">
     @include('admin.layout.nav')
      @include('admin.layout.sidebar')
     <!-- BEGIN #content -->
		<div id="content" class="app-content">
        <div class="row align-items-center mb-3">
        	<div class="col-md-8">
			<h1 class="page-header mb-0">
				Hi, {{$admin->name}} <small class="d-block d-sm-inline mt-1">{{ __('keywords.Here is your admin panel')}}.</small>
			</h1></div>
			<div class="col-md-4 mt-2 mt-md-0 d-flex justify-content-md-end">
                <select class="form-control changeLang" style="max-width: 160px;" aria-label="Change language">
                    <option value="en" {{ session()->get('locale') == 'en'||config('app.locale')=="en" ? 'selected' : '' }}>English</option>
                    <option value="bu" {{ session()->get('locale') == 'bu'||config('app.locale')=="bu" ? 'selected' : '' }}>Bulgarian</option>
                    <option value="in" {{ session()->get('locale') == 'in'||config('app.locale')=="in" ? 'selected' : '' }}>Hindi</option>
                     <option value="ch" {{ session()->get('locale') == 'ch'||config('app.locale')=="ch" ? 'selected' : '' }}>Chinese</option>
                </select>
            </div></div>
                 @yield('content')
             </div>
        <!--footer-->
         @include('admin.layout.footer')

   	        </div>
		<!-- END #content -->

		<!-- BEGIN btn-scroll-top -->
		<a href="#" data-click="scroll-top" class="btn-scroll-top fade"><i class="fa fa-arrow-up"></i></a>
		<!-- END btn-scroll-top -->
	</div>
	<!-- END #app -->
  @yield('line-chart')
  @yield('jQuery')
<script src="{{url('assets/theme_assets/js/app.min.js')}}"></script>  {{-- Jquery 3.5, JqueryUI  all minified, don't include this libraries again  --}}
<script type="text/javascript">
    var url = "{{ route('changeLang') }}";

    $(".changeLang").change(function(){
        window.location.href = url + "?lang="+ $(this).val();
    });
</script>
<script src="{{url('assets/theme_assets/plugins/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/datatables.net-buttons/js/buttons.colVis.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/datatables.net-buttons/js/buttons.flash.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/datatables.net-buttons/js/buttons.html5.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/datatables.net-buttons/js/buttons.print.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js')}}"></script>
<script src="{{url('assets/theme_assets/plugins/bootstrap-table/dist/bootstrap-table.min.js')}}"></script>
@yield('postload-section')
</body>
</html>