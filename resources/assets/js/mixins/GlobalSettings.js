export default {
 computed: {
   isApplicationPublic () {
     // Defines whether or not the application needs a proper login, registration flow and features for logged in users
     return window.isApplicationPublic
   }
 }
}
