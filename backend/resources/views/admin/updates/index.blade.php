<x-app-layout>
    <x-slot name="header">
        <x-ui.page-header title="Guncelleme Yonetimi" subtitle="Force update baslatin ve durumunu takip edin." />
    </x-slot>

    <x-ui.app-shell>
        <x-ui.alert type="warning">
            Bu islem anlik push degildir. Kullanici Outlook'ta yeni mail olusturdugunda veya add-in kontrol yaptiginda uygulanir.
        </x-ui.alert>

        <x-ui.card title="Yeni Force Update" x-data="{ step: 1, scope: '{{ old('scope_key', 'all') }}' }">
            <form method="POST" action="{{ route('admin.updates.store') }}" class="space-y-4">
                @csrf

                <div class="flex flex-wrap gap-2 text-xs">
                    <span class="rounded-full px-2 py-1" :class="step===1 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700'">1. Kapsam</span>
                    <span class="rounded-full px-2 py-1" :class="step===2 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700'">2. Neden ve sure</span>
                    <span class="rounded-full px-2 py-1" :class="step===3 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700'">3. Ozet ve baslat</span>
                </div>

                <div x-show="step===1" class="space-y-3">
                    <label class="block text-sm font-medium text-slate-700">Kapsam sec</label>
                    <x-ui.select name="scope_key" x-model="scope" required>
                        <option value="all">Tum kullanicilar</option>
                        <option value="department">Departman</option>
                        <option value="group">Grup</option>
                        <option value="user">Tek kullanici</option>
                    </x-ui.select>

                    <div x-show="scope==='department'" x-cloak>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Departman</label>
                        <x-ui.select name="department_id">
                            <option value="">Departman secin</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('department_id') == $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div x-show="scope==='group'" x-cloak>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Grup</label>
                        <x-ui.select name="group_id">
                            <option value="">Grup secin</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>{{ $group->name }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>

                    <div x-show="scope==='user'" x-cloak>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kullanici</label>
                        <x-ui.select name="user_id">
                            <option value="">Kullanici secin</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} - {{ $user->email }}</option>
                            @endforeach
                        </x-ui.select>
                    </div>
                </div>

                <div x-show="step===2" x-cloak class="space-y-3">
                    <label class="block text-sm font-medium text-slate-700">Aciklama</label>
                    <x-ui.textarea name="reason" rows="4" placeholder="Guncelleme nedenini aciklayin" required>{{ old('reason') }}</x-ui.textarea>

                    <label class="block text-sm font-medium text-slate-700">TTL</label>
                    <x-ui.select name="ttl_days" required>
                        <option value="1" @selected(old('ttl_days', '1') == '1')>1 gun</option>
                        <option value="3" @selected(old('ttl_days') == '3')>3 gun</option>
                        <option value="7" @selected(old('ttl_days') == '7')>7 gun</option>
                    </x-ui.select>
                </div>

                <div x-show="step===3" x-cloak class="space-y-3">
                    <x-ui.alert type="info">Kayit olusturulduktan sonra durum asagidaki tablodan takip edilir.</x-ui.alert>
                    <div class="grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><span class="text-slate-500">Kapsam:</span> <strong x-text="scope"></strong></div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2"><span class="text-slate-500">TTL:</span> <strong x-text="document.querySelector('[name=ttl_days]')?.value + ' gun'"></strong></div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <x-ui.button type="button" variant="secondary" x-show="step > 1" @click="step--">Geri</x-ui.button>
                    <x-ui.button type="button" x-show="step < 3" @click="step++">Ileri</x-ui.button>
                    <x-ui.button type="submit" x-show="step===3">Force Update Baslat</x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <x-ui.card title="Son Guncelleme Kayitlari">
            @if($updates->isEmpty())
                <x-ui.empty-state title="Henuz guncelleme kaydi yok" description="Ilk force update kaydini yukaridaki sihirbaz ile olusturun." />
            @else
                <x-ui.table>
                    <x-slot name="head">
                        <tr>
                            <th>Kapsam</th>
                            <th>Hedef</th>
                            <th>Neden</th>
                            <th>Durum</th>
                            <th>Etkilenen</th>
                            <th>Tamamlanan</th>
                            <th>Bekleyen</th>
                            <th>Baslangic</th>
                            <th>Bitis/Expire</th>
                            <th>Aksiyon</th>
                        </tr>
                    </x-slot>
                    @foreach($updates as $update)
                        <tr>
                            <td>{{ $update->computed_scope }}</td>
                            <td>{{ $update->computed_target }}</td>
                            <td>{{ $update->reason }}</td>
                            <td><x-ui.badge status="default">{{ $update->computed_status }}</x-ui.badge></td>
                            <td>{{ $update->computed_affected_users }}</td>
                            <td>{{ $update->computed_completed_users }}</td>
                            <td>{{ $update->computed_pending_users }}</td>
                            <td>{{ optional($update->created_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>{{ optional($update->expires_at)?->format('Y-m-d H:i') ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.updates.show', $update) }}"><x-ui.button type="button" variant="secondary">Detay</x-ui.button></a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="10" class="pt-0">
                                <div class="h-2 w-full rounded bg-slate-200">
                                    <div class="h-2 rounded bg-blue-700" style="width: {{ $update->computed_progress }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <x-slot name="footer">{{ $updates->links() }}</x-slot>
                </x-ui.table>
            @endif
        </x-ui.card>
    </x-ui.app-shell>
</x-app-layout>
