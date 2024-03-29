@extends('base')
@section('content')
    <div class="bg-dark text-center">
        <h1 class="text-3xl text-blue-900 dark:text-blue-200 ">Tips</h1>
    </div>
    <div class="container mx-auto w-4/6">
        <div class="p-1 m-2 rounded-lg bg-blue-200 dark:bg-blue-400 " role="alert">
        </div>
        <div class="p-3 grid grid-cols-3 md:grid-cols-4 gap-4">
            @foreach ($tips as $item)
                <div @if ($item->marked) class="border border-green-300 rounded-lg w-fit" @endif>
                    <button
                        onclick="fillin('{{ $item->id }}','{{ $item->for }}','{{ $item->text }}','{{ asset($item->attachment) }}','{{ $item->marked }}','{{ $item->created_at }}')"
                        data-modal-target="default-modal" data-modal-toggle="default-modal"
                        class="block max-w-sm p-4 bg-white border border-gray-200 rounded-lg shadow hover:bg-gray-100 dark:bg-gray-800 dark:border-gray-700 dark:hover:bg-gray-700"
                        type="button">
                        <div class="flex items-center mb-2">
                            <a href="#">
                                <img class="w-10 h-10 rounded-lg" src="{{ asset($item->attachment) }}" alt="noimage">
                            </a>
                            <h5 class="ms-3 text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                                {{ substr($item->text, 0, 10) }} ...</h5>
                            @if (!empty($item->for))
                                <span
                                    class="inline-flex items-center justify-center w-4 h-4 ms-2 text-xs font-semibold text-dark-800 bg-green-700 dark:bg-green-200 rounded-full">
                                    p
                                </span>
                            @endif
                        </div>
                        <p class="text-gray-700 dark:text-gray-400">{{ substr($item->text, 0, 80) }}</p>
                    </button>
                    @if ($item->marked)
                        <div class="flex items-center px-4 py-2 text-sm text-yellow-800 rounded-lg bg-yellow-50 dark:bg-gray-800 dark:text-yellow-300"
                            role="alert">
                            <svg class="flex-shrink-0 inline w-4 h-4 me-2" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M7.8 2c-.5 0-1 .2-1.3.6A2 2 0 0 0 6 3.9V21a1 1 0 0 0 1.6.8l4.4-3.5 4.4 3.5A1 1 0 0 0 18 21V3.9c0-.5-.2-1-.5-1.3-.4-.4-.8-.6-1.3-.6H7.8Z" />
                            </svg>
                            <div>
                                <span class="font-medium">bookmarked</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main modal -->
    <div id="default-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-2xl max-h-full">
            <!-- Modal content -->
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <div class="flex items-center justify-between">
                        <h3 id="modalTitle" class="text-xl font-semibold text-gray-900 dark:text-white me-4">Tip Title</h3>
                        <span id="modalDate"
                            class="bg-blue-100 text-blue-800 text-s font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Date</span>
                    </div>
                    <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="default-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>
                <!-- Modal body -->
                <div class="p-4 md:p-5 space-y-4">

                    <figure
                        class="relative w-1/3 transition-all duration-300 cursor-pointer filter grayscale hover:grayscale-0">
                        <a id="modalImageLink" href="" target="_blank">
                            <img id="modalImage" class="rounded-lg" src="">
                        </a>
                        <figcaption class="absolute px-4 text-lg text-white bg-yellow-800 bottom-0">
                            <p>Evidence</p>
                        </figcaption>
                    </figure>

                </div>
                <div class="p-4 md:p-5 space-y-4">
                    <p id="modalDescription" class="text-base leading-relaxed text-gray-500 dark:text-gray-400">
                        Tip Description
                    </p>
                </div>
                <!-- Modal footer -->
                <div class="flex items-center p-4 md:p-5 border-t border-gray-200 rounded-b dark:border-gray-600">
                    <button data-modal-hide="default-modal" type="button" id="modalBookmarkBtn" onclick="bookmark(event)"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        <svg id="modalBookmark" class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                            pointer-events="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m13 19-6-5-6 5V2a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v17Z" />
                        </svg>
                    </button>
                    <a href="#" id="modalShowParent" hidden><span
                            class="bg-green-100 text-green-800 text-xl font-medium mx-5 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">show
                            parent broadcast</span></a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modalDescription = document.getElementById("modalDescription")
        const modalTitle = document.getElementById("modalTitle")
        const modalImageLink = document.getElementById("modalImageLink")
        const modalImage = document.getElementById("modalImage")
        const modalBookmark = document.getElementById("modalBookmark")
        const modalBookmarkBtn = document.getElementById("modalBookmarkBtn")
        const modalDate = document.getElementById("modalDate")
        const modalShowParent = document.getElementById("modalShowParent")
        const pageAlert = document.getElementById("page_alert")

        function fillin(id, parent, text, img, marked, datetime) {
            modalDescription.innerHTML = text
            modalTitle.innerHTML = text.substring(0, 20) + " ..."
            modalImageLink.href = img
            modalImage.src = img
            modalBookmarkBtn.value = id
            if (parent.length != 0) {
                modalShowParent.hidden = false
                modalShowParent.href = "broadcast_get?id=" + parent
            }
            if (marked == 1) {
                modalBookmark.setAttribute('fill', "currentColor")
            } else {
                modalBookmark.setAttribute('fill', "none")
            }
            modalDate.innerHTML = datetime.substring(0, 10) + " at " + datetime.substring(10) + " UTC"
        }

        async function bookmark(event) {
            const response = await fetch('tip/bookmark?id=' + event.target.value);
            const data = await response.json();
            if (data['status'] == "ok") {
                showAlert("Bookmark Toggled Sussessfuly", 4)
            } else {
                showAlert("Bookmark Toggle Faild", 4)
            }
            setTimeout(() => {
                location.reload()
            }, 2000);
        }
    </script>
@endsection
