@extends('base')

@section('content')
    <div class="container w-3/5 mx-auto bg-white dark:bg-gray-800 p-8">
        <div class="p-2 m-2 text-md text-center text-gray-800 rounded-lg bg-gray-50 dark:bg-gray-400 dark:text-blue-900"
            role="alert">
            <h5 class=" text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{$broadcast->title}}</h5>
        </div>
        <div class="container w-3/4 mx-auto">



            <div class="max-w-full h-full bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
                <a href="{{ asset('storage/' . $broadcast->attachment) }}" target="_blank">
                    <img class="rounded-t-lg h-80 w-full" src="{{ asset('storage/' . $broadcast->attachment) }}"
                        alt="noimage" />
                </a>
                <div class="p-5">
                    <p class="mb-3 font-normal text-gray-700 dark:text-gray-400">{{$broadcast->description}}</p>
                    <a href="#"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Read more
                        <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 14 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M1 5h12m0 0L9 1m4 4L9 9" />
                        </svg>
                    </a>
                </div>
            </div>


        </div>
    </div>
@endsection
