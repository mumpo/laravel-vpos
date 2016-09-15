@if ($vpos)
<html>
<head>
</head>
<body>
<form action="{!! $vpos['url'] !!}" method="POST" name="deliver">
	@foreach ($vpos['inputs'] as $name => $value)
	<input type="hidden" name="{{ $name }}" value="{{ $value }}" />
	@endforeach
</form>
<script language="JavaScript">
	// document.deliver.submit();
</script>
</body>
</html>
@endif
