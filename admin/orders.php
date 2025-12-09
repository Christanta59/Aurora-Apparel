<h2>Daftar Order</h2>

<table border="1" cellpadding="10" width="100%">
<thead>
<tr>
    <th>ID</th>
    <th>Pelanggan</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>
<tbody id="orderTable">
    <tr><td colspan="4" style="text-align:center;">Loading...</td></tr>
</tbody>
</table>

<script>
// AMBIL DATA ORDER
function loadOrders(){
    fetch("../api/recent_orders.php")
    .then(r=>r.json())
    .then(data => {
        let html = "";

        data.forEach(o => {
            html += `
                <tr>
                    <td>${o.id}</td>
                    <td>${o.customer || '-'}</td>
                    <td>
                        <select id="status_${o.id}">
                            <option value="pending" ${o.status==='pending'?'selected':''}>Pending</option>
                            <option value="processing" ${o.status==='processing'?'selected':''}>Processing</option>
                            <option value="shipped" ${o.status==='shipped'?'selected':''}>Shipped</option>
                        </select>
                    </td>
                    <td>
                        <button onclick="save(${o.id})">Simpan</button>
                    </td>
                </tr>
            `;
        });

        document.getElementById("orderTable").innerHTML = html;
    });
}

// SIMPAN STATUS
function save(id){
    const status = document.getElementById("status_" + id).value;

    fetch("../api/update_order.php", {
        method:"POST",
        headers:{ "Content-Type":"application/json" },
        body:JSON.stringify({ id, status })
    })
    .then(r=>r.json())
    .then(res=>{
        if(res.success){
            alert("Status diperbarui!");
            loadOrders();
        } else {
            alert("Gagal update status");
        }
    });
}

loadOrders();
</script>
