<x-mail::message>
    {{-- Greeting --}}
{{--    @if (! empty($greeting))--}}
{{--        # {{ $greeting }}--}}
{{--    @else--}}
{{--        @if ($level === 'error')--}}
{{--            # @lang('Whoops!')--}}
{{--        @else--}}
{{--            # @lang('Hello!')--}}
{{--        @endif--}}
{{--    @endif--}}

    {{-- Intro Lines --}}
    {!! $body !!}

    {{-- Salutation --}}
{{--    @if (! empty($salutation))--}}
{{--        {{ $salutation }}--}}
{{--    @else--}}
{{--        @lang('Regards'),--}}
{{--        {{ $sender->name }}--}}
{{--        {{ $sender->profile->phone_no }}--}}
{{--        {{ $sender->email }}--}}
{{--    @endif--}}
</x-mail::message>
