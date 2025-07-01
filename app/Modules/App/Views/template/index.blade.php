@include('app::template.header')
@include('app::template.navbar')
<!-- Container -->
<div id="container">
  @include($content)
</div>
<!-- End Container -->
@include('app::template.footer')