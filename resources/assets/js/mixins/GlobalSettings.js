export default {
 computed: {
   isApplicationPublic () {
     // Defines whether the application needs a proper login, registration flow and features for logged-in users
     return window.isApplicationPublic
   }
 }
}
