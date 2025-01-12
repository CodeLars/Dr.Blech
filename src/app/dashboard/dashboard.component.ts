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
    console.log("loadCustomers aufgerufen"); // Debugging-Anweisung
    fetch('http://localhost/Dr.Blech/backend/api.php?action=getCustomers')
      .then(response => {
        console.log("Response erhalten:", response); // Debugging-Anweisung
        return response.json();
      })
      .then((data: any[]) => { // Definiere den Typ der data-Variable
        console.log("Daten erhalten:", data); // Debugging-Anweisung
        this.customers = data.map((customer: any) => ({
          id: customer.kundennummer, // Verwende die Großbuchstaben, die in der API-Antwort zurückgegeben werden
          name: `${customer.vorname} ${customer.nachname} (${customer.kundennummer})` // Verwende die Großbuchstaben, die in der API-Antwort zurückgegeben werden
        }));
        console.log("Kunden geladen:", this.customers); // Debugging-Anweisung
        this.updateCustomerDropdown(); // Aktualisiere das Dropdown-Menü
      })
      .catch(error => {
        console.error('Fehler beim Laden der Kunden', error);
      });
  }

  loadOffers(): void {
    console.log("loadOffers aufgerufen"); // Debugging-Anweisung
    fetch('http://localhost/Dr.Blech/backend/api.php?action=getOffers')
      .then(response => {
        console.log("Response erhalten:", response); // Debugging-Anweisung
        return response.json();
      })
      .then((data: any[]) => { // Definiere den Typ der data-Variable
        console.log("Angebote erhalten:", data); // Debugging-Anweisung
        this.updateOffersTable(data); // Aktualisiere die Tabelle
      })
      .catch(error => {
        console.error('Fehler beim Laden der Angebote', error);
      });
  }

  updateOffersTable(offers: any[]): void {
    if (isPlatformBrowser(this.platformId)) {
      const tableBody = document.querySelector('#example tbody');
      if (tableBody) {
        tableBody.innerHTML = ''; // Entferne alle vorhandenen Zeilen
        offers.forEach(offer => {
          const row = document.createElement('tr');
          row.innerHTML = `
            <td>${offer.Blechart}</td>
            <td>${offer.nachname}</td>
            <td>${offer.pauschalbetrag}</td>
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
    selectElement.innerHTML = ''; // Entferne alle vorhandenen Optionen
    this.customers.forEach(customer => {
      const option = document.createElement('option');
      option.value = customer.id.toString(); // Setze nur die Kundennummer als Wert
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
    event.preventDefault(); // Verhindere das Standardverhalten des Formulars

    console.log("onSubmit aufgerufen"); // Debugging-Anweisung

    const formData = {
      newCustomer: this.isNewCustomer,
      existingCustomer: (document.getElementById('existingCustomer') as HTMLSelectElement).value, // Nur die Kundennummer
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

    console.log("data", formData); // Füge formData zur Konsole hinzu, um die Daten zu überprüfen
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
      this.loadOffers(); // Aktualisiere die Tabelle nach dem Erstellen eines Angebots
    })
    .catch(error => {
      console.error('Fehler beim Erstellen des Angebots', error);
    });

    location.reload(); 
  }
}