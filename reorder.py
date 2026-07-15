import re

def reorder_file(file_path):
    with open(file_path, 'r') as f:
        content = f.read()

    # Split sections based on exact comment strings
    parts = re.split(r'(\{\{-- SECTION \d: .*? --\}\})', content)
    
    # parts[0] is everything before Section 1
    header = parts[0]
    
    sections = {}
    for i in range(1, len(parts), 2):
        sec_title = parts[i]
        sec_content = parts[i+1]
        
        if 'Pilih Paket' in sec_title or 'Pilih Varian' in sec_title:
            sections['pilih_paket'] = sec_title + sec_content
        elif 'Komponen Menu' in sec_title or 'Pilih Menu' in sec_title:
            sections['pilih_menu'] = sec_title + sec_content
        elif 'Detail Acara' in sec_title:
            sections['detail_acara'] = sec_title + sec_content
        elif 'Layanan Tambahan' in sec_title:
            sections['layanan_tambahan'] = sec_title + sec_content
        elif 'Ringkasan' in sec_title:
            sections['ringkasan'] = sec_title + sec_content
    
    # We need to split sections['detail_acara'] into Detail Acara and Data Pemesan.
    # The split point is right before `<div>\n <label class="block ...">Nama Pemesan`
    detail_acara_str = sections['detail_acara']
    
    # Find where Nama Pemesan starts
    match = re.search(r'(\s*<div>\s*<label.*?Nama Pemesan)', detail_acara_str)
    if not match:
        print(f"Nama Pemesan not found in {file_path}")
        return
        
    split_idx = match.start(1)
    
    real_detail = detail_acara_str[:split_idx]
    
    # the real detail acara is missing closing div for grid and surface
    real_detail += '\n                    </div>\n                </div>\n\n'
    
    # data pemesan needs opening div for grid and surface
    data_pemesan_content = detail_acara_str[split_idx:]
    # data_pemesan_content still has the closing divs at the end from original section 3
    
    data_pemesan = '''                {{-- SECTION X: Data Pemesan --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-serif text-primary mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold section-number">X</span>
                        Data Pemesan
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">''' + data_pemesan_content
                    
    sections['detail_acara'] = real_detail
    sections['data_pemesan'] = data_pemesan

    # Order desired:
    # 1. Detail Acara
    # 2. Pilih Paket
    # 3. Pilih Menu
    # 4. Data Pemesan
    # 5. Layanan Tambahan (if exists)
    # 6. Ringkasan & Submit
    
    ordered_keys = ['detail_acara', 'pilih_paket', 'pilih_menu', 'data_pemesan']
    if 'layanan_tambahan' in sections:
        ordered_keys.append('layanan_tambahan')
    ordered_keys.append('ringkasan')
    
    final_content = header
    for idx, k in enumerate(ordered_keys, 1):
        sec = sections[k]
        # Replace the section number in the comment
        sec = re.sub(r'\{\{-- SECTION \d:', f'{{{{-- SECTION {idx}:', sec)
        # Replace the section number in the span (if it exists)
        # Using a regex to find <span class="...w-7 h-7... bg-primary ...">\d+</span> or X
        sec = re.sub(r'(<span[^>]*w-7 h-7[^>]*>)\s*(\d+|X)\s*(</span>)', rf'\g<1>{idx}\g<3>', sec)
        
        final_content += sec

    with open(file_path, 'w') as f:
        f.write(final_content)
    print(f"Successfully reordered {file_path}")

reorder_file('resources/views/pesanan/catering/create.blade.php')
reorder_file('resources/views/pesanan/nasibox/create.blade.php')

