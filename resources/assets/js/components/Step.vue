<template>
  <div class="step" :class="{active:current==number, muted:current!=number}">
    <slot>Content expected</slot>
  </div>
</template>

<script>
export default {
	props: ['number', 'current']
}
</script>