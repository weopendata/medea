<template>
	<tr>
		<td class="tr-toggle" :class="{on:user.verified}" @click="verify"></td>
		<td style="text-align:left"><a :href="uri">{{ user.firstName }}</a></td>
		<td style="text-align:left"><a :href="uri">{{ user.lastName }}</a></td>
		<td class="tr-toggle" :class="{on:is.detectorist}" @click="toggle('detectorist')"></td>
		<td class="tr-toggle" :class="{on:is.vondstexpert}" @click="toggle('vondstexpert')"></td>
		<td class="tr-toggle" :class="{on:is.registrator}" @click="toggle('registrator')"></td>
		<td class="tr-toggle" :class="{on:is.validator}" @click="toggle('validator')"></td>
		<td class="tr-toggle" :class="{on:is.onderzoeker}" @click="toggle('onderzoeker')"></td>
		<td class="tr-toggle" :class="{on:is.administrator}" @click="toggle('administrator')"></td>
	</tr>
</template>

<script>
export default {
	props: ['user'],
	computed: {
		is () {
			var obj = {}
			for (var i = this.user.personType.length - 1; i >= 0; i--) {
				obj[this.user.personType[i]] = true
			}
			return obj
		},
		uri () {
			return '/persons/' + this.user.id
		}
	},
	methods: {
		toggle (role) {
			var i = this.user.personType.indexOf(role);
			if(i !== -1) {
				this.user.personType.splice(i, 1);
			} else {
				this.user.personType.push(role)
			}
			this.$http.post('/persons/' + this.user.id, {
				_method: 'PUT',
				id: this.user.id,
				personType: this.user.personType
			})
		},
		verify () {
			this.user.verified = !this.user.verified
			this.$http.post('/persons/' + this.user.id, {
				_method: 'PUT',
				id: this.user.id,
				verified: this.user.verified
			})
		}
	}
}
</script>