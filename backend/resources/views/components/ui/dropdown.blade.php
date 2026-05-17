<div
    x-data="{
        open: false,
        top: 0,
        left: 0,
        width: 176,
        place() {
            const rect = this.$refs.trigger.getBoundingClientRect();
            this.top = rect.bottom + 8;
            this.left = Math.max(8, rect.right - this.width);
        },
        toggle() {
            this.open = !this.open;
            if (this.open) this.$nextTick(() => this.place());
        }
    }"
    @keydown.escape.window="open=false"
    @scroll.window="if (open) place()"
    @resize.window="if (open) place()"
    class="inline-block"
>
    <div x-ref="trigger" @click="toggle()">{{ $trigger }}</div>

    <div
        x-show="open"
        x-cloak
        @click.outside="open=false"
        x-transition.opacity
        class="fixed z-50 w-44 rounded-lg border border-slate-200 bg-white p-1 shadow"
        :style="`top:${top}px;left:${left}px`"
    >
        {{ $slot }}
    </div>
</div>
