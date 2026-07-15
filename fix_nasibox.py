import re

def fix_nasi_box():
    with open('resources/views/pesanan/nasibox/create.blade.php', 'r') as f:
        content = f.read()
    
    # We want to find the section Data Pemesan (SECTION 4) and move Alamat above Map.
    
    # Let's just find the div for Alamat Lengkap and move it.
    
    # Actually, the file order wasn't fully fixed for nasibox because the previous script failed.
    # Let's fix Nasi Box structure first as requested before, then move the Alamat text area.
    pass

fix_nasi_box()
