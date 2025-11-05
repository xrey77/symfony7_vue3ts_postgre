<script lang="ts">
  import { defineComponent} from 'vue';
  import Login from '../components/Login.vue';
  import Register from '../components/Register.vue';
  import axios from 'axios';
  import '../styles/app.scss'
const api = axios.create({
  baseURL: "https://localhost:8000",
  headers: {'Accept': 'application/json',
            'Content-Type': 'application/json'}
})

export default defineComponent({
  name: 'Header-Page',
  components: {
    Login,
    Register,
  },
  data() {
      return {
        username: '',
        userpic: ''
      }
  },
  mounted() {
    const usrname = sessionStorage.getItem('USERNAME');
    if (usrname !== null) {
      this.username = usrname
    }
    const usrpic = sessionStorage.getItem('USERPIC');
    if (usrpic !== null) {
      this.userpic = usrpic
    }       
  },
  methods: {
    logout: function() {

      sessionStorage.removeItem('USERID');
      sessionStorage.removeItem('USERNAME');
      sessionStorage.removeItem('USERPIC');
      sessionStorage.removeItem('TOKEN');    

      api.post("/app_logout").then((res: any) => {
          this.$router.push('/');
      }, (error: any) => {      
          return;
      });    
      location.reload();

    }
  }
});

</script>

<style lang="scss" scoped>

.nav-top {
  margin-top: -10px !important;
}
.burger {
  background-color: white !important;
  border-radius: 20% !important;
}
</style>




<template>
    <nav class="navbar nav-top navbar-expand-lg bg-dark">
        <div class="container-fluid">
          <RouterLink class="navbar-brand" to="/"><img class="logo" src="/images/logo.png" alt=""/></RouterLink>
          <button class="navbar-toggler" type="button"  data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu" aria-controls="offcanvasWithBothOptions">
            <span class="navbar-toggler-icon burger"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <RouterLink class="nav-link text-white" aria-current="page" to="/about">About Us</RouterLink>
              </li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Products
                </a>
                <ul class="dropdown-menu">
                  <li><RouterLink class="dropdown-item" to="/listproducts">Products List</RouterLink></li>
                  <li><RouterLink class="dropdown-item" to="/listcatalogs">Products Catalog</RouterLink></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><RouterLink class="dropdown-item" to="/searchproduct">Product Search</RouterLink></li>
                </ul>
              </li>
              <li class="nav-item">
                <RouterLink class="nav-link text-white" to="/contact">Contact Us</RouterLink>
              </li>
            </ul>
              <ul v-if="username === ''" class="navbar-nav mr-auto">
                <li class="nav-item">
                  <a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#staticLogin">Login</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#staticRegister">Register</a>
                </li>
              </ul>          
              <ul v-else class="navbar-nav mr-auto">
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle  text-white" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img class="user" v-bind:src="userpic" />{{ username }}
                  </a>
                  <ul class="dropdown-menu">
                    <li data-bs-dismiss="offcanvas">
                      <a @click="logout" class="dropdown-item" href="/#">LogOut</a>
                    </li>
                    <li class="nav-item"><hr/></li>
                    <li data-bs-dismiss="offcanvas">
                      <RouterLink class="dropdown-item" to="/profile">Profile</RouterLink>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li data-bs-dismiss="offcanvas">
                      <RouterLink class="dropdown-item" to="/#">Messenger</RouterLink>
                    </li>
                  </ul>
                </li>              
              </ul>        


          </div>
        </div>
      </nav>    
    <!--START OFFCANVAS MENU -->
    <div class="offcanvas offcanvas-end" data-bs-scroll="true" tabindex="-1" id="offcanvasMenu" aria-labelledby="offcanvasWithBothOptionsLabel">
      <div class="offcanvas-header bg-primary">
        <h5 class="offcanvas-title text-white" id="offcanvasWithBothOptionsLabel">Drawer Menu</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">

        <ul class="nav flex-column">
          <li class="nav-item" data-bs-dismiss="offcanvas">
            <RouterLink class="nav-link active" to="/about">About Us</RouterLink>
          </li>
          <li class="nav-item"><hr/></li>
          <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                  Products
                </a>
                <ul class="dropdown-menu">
                  <li data-bs-dismiss="offcanvas">
                    <RouterLink class="dropdown-item" to="/listproducts">Product List</RouterLink>
                </li>
                  <li data-bs-dismiss="offcanvas">
                    <RouterLink class="dropdown-item" to="/listcatalogs">Catalogs</RouterLink>                
                </li>
                  <li><hr class="dropdown-divider"></li>
                  <li data-bs-dismiss="offcanvas">
                    <RouterLink class="dropdown-item" to="/searchproduct">Search Product</RouterLink>
                </li>
                </ul>
              </li>
              <li class="nav-item"><hr/></li>

              <li class="nav-item" data-bs-dismiss="offcanvas">
                <RouterLink class="nav-link active" to="/contact">Contact Us</RouterLink>
              </li>
              <li class="nav-item"><hr/></li>

            </ul>
            <div v-if="username === ''">
              <ul class="nav flex-column">
                <li class="nav-item" data-bs-dismiss="offcanvas">
                  <a class="nav-link active" href="/#" data-bs-toggle="modal" data-bs-target="#staticLogin">Login</a>
                </li>
                <li class="nav-item"><hr/></li>

                <li class="nav-item" data-bs-dismiss="offcanvas">
                  <a class="nav-link active" href="/#" data-bs-toggle="modal" data-bs-target="#staticRegister">Register</a>
                </li>            
              </ul>
            </div>
            <ul v-if="username !== ''"  class="navbar-nav mr-auto">
                  <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <img class="user"  v-bind:src="userpic" />{{ username }}
                    </a>
                    <ul class="dropdown-menu">
                      <li data-bs-dismiss="offcanvas">
                        <a @click="logout" class="dropdown-item" href="/#">LogOut</a>
                      </li>
                      <li class="nav-item"><hr/></li>
                      <li data-bs-dismiss="offcanvas">
                        <RouterLink class="dropdown-item" to="/profile">Profile</RouterLink>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li data-bs-dismiss="offcanvas">
                        <RouterLink class="dropdown-item" to="/#">Messenger</RouterLink>
                      </li>
                    </ul>
                  </li>              
        </ul>        

      </div>
    </div>
    <RouterView/>
 
    <!-- END OFFCANVAS MENU-->
    
      <Login/>
      <Register/>

</template>

