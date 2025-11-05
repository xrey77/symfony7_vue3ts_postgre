import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import { createRouter, createWebHistory } from 'vue-router';
import { createApp } from 'vue';
import Home from './pages/Home.vue';
import About from './pages/About.vue';
import Contact from './pages/Contact.vue';
import Profile from './pages/Profile.vue';
import List from './pages/List.vue';
import Catalogs from './pages/Catalogs.vue';
import Search from './pages/Search.vue';
import AppLayout from './Layouts/AppLayout.vue';
import './styles/app.scss';
// import App from './controllers/App.vue';


const root = document.getElementById('vue-root');
// if (root) {
//   createApp(App).mount(root);
// }

// // Import your global CSS file

// // Add the rest of your app's initialization code here
// console.log('Vue with TypeScript is running!');

const router = createRouter({
  history: createWebHistory(),
  routes: [
      // { path: "/:pathMatch(.*)*",component: NotFound },
      { path: "/", name: "home", component: Home },
      { path: "/about",name: "about",component: About },
      { path: "/contact",name: "contact",component: Contact },
      { path: "/profile",name: "profile",component: Profile },
      { path: "/listproducts",name: "listproducts",component: List},
      { path: "/listcatalogs",name: "listcatalogs",component: Catalogs },
      { path: "/searchproduct",name: "searchproduct",component: Search },
  ]
});
if (root) {
  createApp(AppLayout).use(router).mount(root);
}
