<template>
	<tr>
		<td style="text-align:left"><a :href="uri">{{ user.firstName }} {{ user.lastName }}</a></td>
		<td class="tr-toggle" :class="{on:is.detectorist}" @click="toggle('detectorist')"></td>
		<td class="tr-toggle" :class="{on:is.vonstexpert}" @click="toggle('vonstexpert')"></td>
		<td class="tr-toggle" :class="{on:is.registrator}" @click="toggle('registrator')"></td>
		<td class="tr-toggle" :class="{on:is.validator}" @click="toggle('validator')"></td>
		<td class="tr-toggle" :class="{on:is.administrator}" @click="toggle('administrator')"></td>
	</tr>
</template>

<script>
export default {
	props: ['user'],
	computed: {
		is () {
			var obj = {}
			for (var i = this.user.roles.length - 1; i >= 0; i--) {
				obj[this.user.roles[i]] = true
			}
			return obj
		},
		uri () {
			return '/users/' + this.user.id
		}
	},
	methods: {
		toggle (role) {
			var i = this.user.roles.indexOf(role);
			if(i !== -1) {
				this.user.roles.splice(i, 1);
			} else {
				this.user.roles.push(role)
			}


			this.$http.post('/users/' + this.user.id, {
				_method: 'PUT',
				id: this.user.id,
				roles: this.user.roles
			})
		}
	}
}
</script>