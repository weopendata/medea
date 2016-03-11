<template>
<div>
	<h2>{{cls.description}}</h2>
	<dl>
		<dt>Cultuur</dt>
		<dd>{{cls.culture}}</dd>
	</dl>
  <pre>{{cls|json}}</pre>
</div>
</template>

<script>
export default {
  props: ['cls'],
  components: {
  }
}
</script>