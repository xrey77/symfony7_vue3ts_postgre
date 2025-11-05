<template>
<div class="container-fluid mb-5">
    <h2>Search Product</h2>    
    <form class="row g-3" @submit.prevent="submitSearch" autocomplete="off">
        <div class="col-auto">
          <input type="text" required class="form-control-sm" v-model="search" name="search" placeholder="enter Product keyword">
          <div class="text-danger">{{message}}</div>
        </div>
        <div class="col-auto">
          <button type="submit" class="btn btn-primary btn-sm mb-3">search</button>
        </div>
    </form>

    <div class="container mb-4">
        <div class="card-group">
            <div v-for="prod in prodsearch" :key="prod['id']" class="col-md-4">
                <div class="card mx-3">
                    <img v-bind:src="prod['productpicture']" class="card-img" alt=""/>
                    <div class="card-body cardbody-height">
                        <h5 class="card-title">Description</h5>
                        <p class="card-text">{{ prod['descriptions'] }}</p>
                    </div>
                    <div class="card-footer price-size">
                        <p class="card-text text-danger"><span class="text-dark">PRICE :</span>&nbsp;<strong>&#8369;{{ toDecimal(prod['sellprice']) }}</strong></p>
                    </div>  
                </div>
            </div>
        
        </div>    
    </div>

</div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';

const api = axios.create({
    baseURL: "https://127.0.0.1:8000",
    headers: {'Accept': 'application/json',
            'Content-Type': 'application/json'}
});

const search = ref<string>('');
const prodsearch = ref([]);
const message = ref<string>('');

const getProdsearch = async (key: string) => {
    message.value = "wait..searching...";
    await api.get(`/api/productsearch/${key}`)
    .then((res: any) => {
        prodsearch.value = res.data;

    }, (error: any) => {
        message.value = error.response.data.message;
        setTimeout(() => {
            message.value = '';
            prodsearch.value = []
        }, 3000);
        return;
    });      
    setTimeout(() => {
            message.value = '';
        }, 1000);

}

const submitSearch = () => {
    getProdsearch(search.value);
}

const toDecimal = (number: number) => {
  const formatter = new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
  return formatter.format(number);
};

</script>