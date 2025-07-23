@extends('layouts.auth')

@section('login')

<div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6 text-center mb-5">
                        <h2 class="heading-section"><b></b>OPTIK MELATI</b></h2>
                    </div>
                </div>
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-4">
                        <div class="login-wrap p-0">
                            <h3 class="mb-4 text-center">Silahkan Login</h3>
                            <form action="{{ route('login') }}" method="post" class="signin-form">
                                @csrf
                                <div class="form-group">
                                    <input
                                        type="text"
                                        class="form-control"
                                        placeholder="Email"
                                        name="email"
                                        required
                                    />
                                </div>
                                <div class="form-group">
                                    <input
                                        id="password-field"
                                        type="password"
                                        class="form-control"
                                        placeholder="Password"
                                        name="password"
                                        required
                                    />
                                    <span
                                        toggle="#password-field"
                                        class="fa fa-fw fa-eye field-icon toggle-password"
                                    ></span>
                                </div>
                                <div class="form-group">
                                    <button
                                        type="submit"
                                        class="form-control btn btn-primary submit px-3"
                                    >
                                        Sign In
                                    </button>
                                </div>
                                <div class="form-group d-md-flex">
                                    <div class="w-50">
                                        <label
                                            class="checkbox-wrap checkbox-primary"
                                            >Remember Me
                                            <input type="checkbox" checked />
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="w-50 text-md-right">
                                        <a href="#" style="color: #fff"
                                            >Forgot Password</a
                                        >
                                    </div>
                                </div>
                            </form>
                           
                        </div>
                    </div>
                </div>
            </div>
@endsection