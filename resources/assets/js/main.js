window.csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

$.ajaxSetup({
  headers: {
    'X-CSRF-TOKEN': window.csrf
  }
})

Vue.http.headers.common['X-CSRF-TOKEN'] = window.csrf