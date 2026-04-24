@extends('layouts.app')
@section('title', 'Submit an issue')

@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-semibold mb-6">Submit an issue</h1>

        <form method="POST" action="{{ route('issues.store') }}" class="space-y-5 bg-white border border-slate-200 rounded-lg p-6">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('title') ring-1 ring-red-400 @enderror">
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea name="description" rows="6" required
                          class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm @error('description') ring-1 ring-red-400 @enderror">{{ old('description') }}</textarea>
                <p class="mt-1 text-xs text-slate-500">At least 20 characters. Describe what happened and anything the agent should know.</p>
                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Priority</label>
                    <select name="priority" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($priorities as $p)
                            <option value="{{ $p->value }}" @selected(old('priority') === $p->value)>{{ $p->value }}</option>
                        @endforeach
                    </select>
                    @error('priority') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select name="category" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($categories as $c)
                            <option value="{{ $c->value }}" @selected(old('category') === $c->value)>{{ $c->value }}</option>
                        @endforeach
                    </select>
                    @error('category') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Due at <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="datetime-local" name="due_at" value="{{ old('due_at') }}"
                       class="rounded-md border border-slate-300 px-3 py-2 text-sm">
                @error('due_at') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('issues.index') }}" class="text-sm text-slate-600 hover:text-slate-900">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-500">
                    Submit issue
                </button>
            </div>
        </form>
    </div>
@endsection
