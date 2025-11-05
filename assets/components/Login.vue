<template>
    <div className="modal fade" id="staticLogin" data-bs-backdrop="static" data-bs-keyboard="false" tabIndex={-1} aria-labelledby="staticLoginLabel" aria-hidden="true">
        <div className="modal-dialog modal-sm modal-dialog-centered">
          <div className="modal-content">
            <div className="modal-header bg-primary">
              <h1 className="modal-title fs-5 text-white" id="staticLoginLabel">User's SignIn</h1>
              <button id="closeLogin" @click="closeLogin" type="button" className="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div className="modal-body">
              <form @submit.prevent="submitLogindata" autoComplete='off'>
                  <div className="mb-3">
                      <input type="text" required v-model="username" :disabled="isDisabled" className="form-control" id="uname" placeholder="enter Username"/>
                  </div>            
                  <div className="mb-3">
                      <input type="password" required v-model="password" :disabled="isDisabled" className="form-control" id="pword" placeholder="enter Password"/>
                  </div>            
                  <button type="submit" className="btn btn-primary mx-1" :disabled="isDisabled">signin</button>
                  <button id="loginReset" type="reset" @click="resetLogin"  className="btn btn-primary">reset</button>
                  <button id="mfa" type="button" className="btn btn-primary mx-1 d-none" data-bs-toggle="modal" data-bs-target="#staticMfa">mfa</button>            
              </form>
            </div>
            <div className="modal-footer">
              <div className='w-100 text-center text-danger'>{{message}}</div>
            </div>
          </div>
        </div>
      </div>        
      <Mfa/>
</template>

<script lang="ts">
import axios from 'axios';
import * as $ from 'jquery';
import Mfa from './Mfa.vue';
import { defineComponent } from 'vue';

const api = axios.create({
  baseURL: "https://127.0.0.1:8000",
  headers: {'Accept': 'application/json',
            'Content-Type': 'application/json'}
});

interface loginResponse {

}

export default defineComponent({
    name: 'Login-Page',
    components: {
        Mfa
    },
    data() {
        return {
            username: '',
            password: '',
            message: '',
            isDisabled: false
        };
    },
    methods: {
        submitLogindata: function() {
                this.isDisabled = true;
                this.message = "please wait...";
                const data =JSON.stringify({ username: this.username, password: this.password });
                api.post("/api/login", data)
                .then((res: any) => {
                    if (res.data.qrcodeurl !== null) {
                        window.sessionStorage.setItem('USERID',res.data.id);
                        window.sessionStorage.setItem('TOKEN',res.data.token);
                        window.sessionStorage.setItem('ROLE',res.data.roles[0]);
                        window.sessionStorage.setItem('USERPIC',res.data.userpicture);
                        this.loginFormAuthentication();
                        setTimeout(() => {
                            this.message = '';
                            this.isDisabled = false;
                            $("#loginReset").trigger('click');
                            $("#mfa").trigger('click');                            
                        }, 5000);
                    } else {
                        window.sessionStorage.setItem('USERID',res.data.id);
                        window.sessionStorage.setItem('USERNAME',res.data.username);
                        window.sessionStorage.setItem('TOKEN',res.data.token);                        
                        window.sessionStorage.setItem('ROLE',res.data.roles[0]);
                        window.sessionStorage.setItem('USERPIC',res.data.userpicture);
                        this.loginFormAuthentication();
                        setTimeout(() => {
                            this.message = '';
                            this.isDisabled = false;
                            $("#closeLogin").trigger('click');
                        }, 6000);            
                        window.location.reload();
                    }
                }, (error) => {
                    this.message = error.response.data.message;
                    setTimeout(() => {
                        this.message = '';
                        this.isDisabled = false;
                    }, 3000);
                    return;
                });
            },
            loginFormAuthentication: async function() {
                const jsonData =JSON.stringify({ username: this.username, password: this.password });
                await api.post("/api/login_check", jsonData)
                .then((res: any) => {
                    // const token = res.data.token; 
                    this.message ='User Authentication successfull..';

                }, (error: any) => {
                    this.message = error.response.data.message;
                });
            },
            closeLogin: function() {
                this.message = '';
                this.isDisabled = false;
                $("#loginReset").trigger('click');
                // window.location.reload();
            },
            resetLogin: function() {
                this.message = '';
                this.username = '';
                this.password = '';
                this.isDisabled = false;
            }
    },



});
</script>