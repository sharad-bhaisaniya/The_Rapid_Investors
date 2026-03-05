<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Metawish Admin') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-RXf+QSDCUQs6Q0mBuNre4GXhF6lG1F54eLhM651c01C9vrLpzjAU6RzGJQ8h9vDOMkMt2rt7NmexGk4xg1L0Q=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="{{ asset('public/assets/js/admin_sidebar.js') }}"></script>
    <script defer src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>


    <!-- ADD PUSHER HERE -->
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>

    <style>
        .main-container {
            overflow: scroll;
            scrollbar-width: none;
            max-height: 85vh;
        }
    </style>

    @stack('scripts')

</head>

<body class="bg-white">
    <div class="min-h-screen flex">

        <!-- Sidebar -->
        @include('components.admin_sidebar')

        <!-- Main content -->
        <div class="flex-1 flex flex-col">

            <!-- Header -->
            @include('components.admin_header')

            <!-- Page Content -->
            <main class="flex-1 p-4 main-container">
                @yield('content')
            </main>          

            <!-- ================= CHAT FLOATING BUTTON ================= -->
             @include('layouts.partials.admin_chatbar')


            @include('components.admin_footer')
        </div>



    </div>
<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>


</body>

</html>
