<script setup>
    import { ref } from 'vue';
    import axios from 'axios';

    const api = axios.create({
        baseURL: "https://localhost:8000",
        headers: {'Accept': 'application/json',
                  'Content-Type': 'application/json'}
    });

    const message = ref('');
    const totalrecords = ref(0);
    const page = ref(1);
    const totpage = ref(0);
    const products = ref([]);


    function getProducts(pg) {
       api.get(`/api/productlist/${pg}`).then((res) => {
          products.value = res.data.products;
          totalrecords.value = res.data.totalrecs;
          page.value = res.data.page;
          totpage.value = res.data.totpage;
      }, (error) => {
              message.value = error.response.data.message;
      });    
    }

    getProducts(1);

    const nextPage = (event) => {
      event.preventDefault();
      if (page.value == totpage.value) {
          return;
      }
      page.value = page.value + 1;
      getProducts(page.value);
      return;
    }

    const prevPage = (event) => {
      event.preventDefault();
      if (page.value == 1) {
      return;
      }
      page.value = page.value - 1;
      getProducts(page.value);
      return;    
    }

    const firstPage = (event) => {
      event.preventDefault();
      page.value = 1;
      getProducts(page.value);
      return;    
    }

    const lastPage = (event) => {
      event.preventDefault();
      page.value = totpage.value;
      getProducts(page.value);
      return;    
    }

    const toDecimal = (nos) => {
      const formatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
      });
      return formatter.format(nos);
    }

</script>

<template>
  <div class="container-fluid">
    <h1 class="text-center">Product List</h1>
    <div v-if="totpage==0" class="text-danger text-center">{{message}}</div>
    <div v-else-if="totpage >1" class="text-danger">{{message}}</div>
    <table class="table table-hover table-success table-striped">
        <thead>
          <tr>
            <th class="bg-primary text-white" scope="col">#</th>
            <th class="bg-primary text-white" scope="col">Descriptions</th>
            <th class="bg-primary text-white" scope="col">Stocks</th>
            <th class="bg-primary text-white" scope="col">Unit</th>
            <th class="bg-primary text-white" scope="col">Price</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in products" :key="item['id']">
            <td>{{ item['id'] }}</td>
            <td>{{ item['descriptions'] }}</td>
            <td>{{ item['qty'] }}</td>
            <td>{{ item['unit'] }}</td>
            <td>&#8369;{{ toDecimal(item['sellprice']) }}</td>
          </tr>
        </tbody>
    </table>    

      <nav aria-label="Page navigation example">
        <ul class="pagination">
          <li class="page-item"><a @click="lastPage" class="page-link" href="#">Last</a></li>
          <li class="page-item"><a @click="prevPage" class="page-link" href="#">Previous</a></li>
          <li class="page-item"><a @click="nextPage" class="page-link" href="#">Next</a></li>
          <li class="page-item"><a @click="firstPage" class="page-link" href="#">First</a></li>
          <li class="page-item page-link text-danger">Page&nbsp;{{ page }} of&nbsp;{{ totpage }}</li>
        </ul>
      </nav>
      <div class="tot-rec">Total Records : {{ totalrecords }}</div>
    </div>
</template>

