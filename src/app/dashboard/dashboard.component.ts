import { Component, AfterViewInit, Inject, PLATFORM_ID } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrls: ['./dashboard.component.scss']
})
export class DashboardComponent implements AfterViewInit {
  isNewCustomer = false;
  isCustomAmount = false;
  customers: any[] = [];

  constructor(@Inject(PLATFORM_ID) private platformId: Object) {}

  ngAfterViewInit(): void {
    if (isPlatformBrowser(this.platformId)) {
      import('jquery').then(({ default: $ }) => {
        import('datatables.net').then(() => {
          import('datatables.net-bs5').then(() => {
            ($('#example') as any).DataTable();
          });
        });
      });
      this.loadCustomers();
      this.loadOffers();
    }
  }

  loadCustomers(): void {
    console.log("loadCustomers aufgerufen"); 
    fetch('http://localhost/Dr.Blech/backend/api.php?action=getCustomers')
      .then(response => {
        console.log("Response erhalten:", response); 
        return response.json();
      })
      .then((data: any[]) => { 
        console.log("Daten erhalten:", data); 
        this.customers = data.map((customer: any) => ({
          id: customer.kundennummer, 
          name: `${customer.vorname} ${customer.nachname} (${customer.kundennummer})` 
        }));
        console.log("Kunden geladen:", this.customers); 
        this.updateCustomerDropdown(); 
      })
      .catch(error => {
        console.error('Fehler beim Laden der Kunden', error);
      });
  }

  loadOffers(): void {
    console.log("loadOffers aufgerufen"); 
    fetch('http://localhost/Dr.Blech/backend/api.php?action=getOffers')
      .then(response => {
        console.log("Response erhalten:", response); 
        return response.json();
      })
      .then((data: any[]) => { 
        console.log("Angebote erhalten:", data); 
        this.updateOffersTable(data); 
      })
      .catch(error => {
        console.error('Fehler beim Laden der Angebote', error);
      });
  }

  updateOffersTable(offers: any[]): void {
    if (isPlatformBrowser(this.platformId)) {
      const tableBody = document.querySelector('#example tbody');
      if (tableBody) {
        tableBody.innerHTML = ''; 
        offers.forEach(offer => {
          const row = document.createElement('tr');
          const formattedAmount = offer.pauschalbetrag.toLocaleString('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).replace('.', ',');
          row.innerHTML = `
            <td>${offer.Blechart}</td>
            <td>${offer.nachname}</td>
            <td>${formattedAmount} €</td>
          `;
          tableBody.appendChild(row);
        });
      } else {
        console.error('Tabelle oder tbody-Element nicht gefunden');
      }
    }
  }
  updateCustomerDropdown(): void {
    const selectElement = document.getElementById('existingCustomer') as HTMLSelectElement;
    selectElement.innerHTML = ''; 
    this.customers.forEach(customer => {
      const option = document.createElement('option');
      option.value = customer.id.toString();
      option.text = customer.name;
      selectElement.appendChild(option);
    });
  }

  toggleCustomerType(event: Event): void {
    this.isNewCustomer = (event.target as HTMLInputElement).checked;
  }

  toggleProcessingSteps(event: Event): void {
    const value = (event.target as HTMLInputElement).value;
    this.isCustomAmount = value !== '' && parseFloat(value) >= 50 && parseFloat(value) <= 10000;
  }

  onSubmit(event: Event): void {
    event.preventDefault(); 

    console.log("onSubmit aufgerufen");

    const formData = {
      newCustomer: this.isNewCustomer,
      existingCustomer: (document.getElementById('existingCustomer') as HTMLSelectElement).value, 
      blechart: (document.getElementById('blechart') as HTMLSelectElement).value,
      material: (document.getElementById('material') as HTMLSelectElement).value,
      width: (document.getElementById('width') as HTMLInputElement).value,
      length: (document.getElementById('length') as HTMLInputElement).value,
      thickness: (document.getElementById('thickness') as HTMLInputElement).value,
      customAmount: (document.getElementById('customAmount') as HTMLInputElement).value,
      cutting: (document.getElementById('cutting') as HTMLInputElement).checked,
      stamping: (document.getElementById('stamping') as HTMLInputElement).checked,
      bending: (document.getElementById('bending') as HTMLInputElement).checked,
      surfaceTreatment: (document.getElementById('surfaceTreatment') as HTMLInputElement).checked,
      milling: (document.getElementById('milling') as HTMLInputElement).checked,
      quantity: (document.getElementById('quantity') as HTMLInputElement).value
    };

    console.log("data", formData); 
    fetch('http://localhost/Dr.Blech/backend/api.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
      console.log('Angebot erfolgreich erstellt', data);
      this.loadOffers(); 
    })
    .catch(error => {
      console.error('Fehler beim Erstellen des Angebots', error);
    });

    location.reload(); 
  }
}