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
  numberCache = "";
  customers: any[] = [];
  materials: any[] = [];

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
      this.loadMaterials();
    }
  }

  loadCustomers(): void {
    console.log("loadCustomers aufgerufen"); 
    fetch('http://localhost/Dr.Blech/backend/api/index.php/api/customers')
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
    fetch('http://localhost/Dr.Blech/backend/api/index.php/api/offers')
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

  loadMaterials(): void {
    console.log("loadMaterials aufgerufen"); 
    fetch('http://localhost/Dr.Blech/backend/api/index.php/api/materials')
      .then(response => {
        console.log("Response erhalten:", response); 
        return response.json();
      })
      .then((data: any[]) => { 
        console.log("Materialien erhalten:", data); 
        this.materials = data.map((material: any) => ({
          id: material.Id, // Verwende 'Id' anstelle von 'id'
          name: material.name 
        }));
        console.log("Materialien geladen:", this.materials); 
        this.updateMaterialDropdown(); 
      })
      .catch(error => {
        console.error('Fehler beim Laden der Materialien', error);
      });
  }

  updateOffersTable(offers: any[]): void {
    if (isPlatformBrowser(this.platformId)) {
      const tableBody = document.querySelector('#example tbody');
      if (tableBody) {
        tableBody.innerHTML = ''; 
        if (Array.isArray(offers)) {
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
          console.error('Keine Angebote vorhanden');
        }
      } else {
        console.error('Tabelle oder tbody-Element nicht gefunden');
      }
    }
  }

  updateCustomerDropdown(): void {
    const selectElement = document.getElementById('existingCustomer') as HTMLSelectElement;
    selectElement.innerHTML = ''; 
        // Create placeholder option with disabled attribute
        const placeholderOption = new Option('Kunde auswählen', '');
        placeholderOption.disabled = true;
        placeholderOption.selected = true;
        selectElement.appendChild(placeholderOption);

    this.customers.forEach(customer => {
      const option = document.createElement('option');
      option.value = customer.id.toString(); 
      option.text = customer.name;
      selectElement.appendChild(option);
    });
  }

  updateMaterialDropdown(): void {
    const selectElement = document.getElementById('material') as HTMLSelectElement;
    selectElement.innerHTML = ''; 

    const placeholderOption = new Option('Material auswählen', '');
    placeholderOption.disabled = true;
    placeholderOption.selected = true;
    selectElement.appendChild(placeholderOption);

    this.materials.forEach(material => {
      const option = document.createElement('option');
      option.value = material.id.toString(); 
      option.text = material.name;
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
  
    const formData: any = {
      blechart: (document.getElementById('blechart') as HTMLSelectElement)?.value,
      material: (document.getElementById('material') as HTMLSelectElement)?.value,
      width: (document.getElementById('width') as HTMLInputElement)?.value,
      length: (document.getElementById('length') as HTMLInputElement)?.value,
      thickness: (document.getElementById('thickness') as HTMLInputElement)?.value,
      cutting: (document.getElementById('cutting') as HTMLInputElement)?.checked,
      stamping: (document.getElementById('stamping') as HTMLInputElement)?.checked,
      bending: (document.getElementById('bending') as HTMLInputElement)?.checked,
      surfaceTreatment: (document.getElementById('surfaceTreatment') as HTMLInputElement)?.checked,
      milling: (document.getElementById('milling') as HTMLInputElement)?.checked,
      quantity: (document.getElementById('quantity') as HTMLInputElement)?.value
    };
    //kundenummer holen aus select
    const customerElement = document.getElementById('existingCustomer') as HTMLSelectElement;
    this.numberCache = customerElement?.value;
    // Überprüfe, ob ein individueller Betrag angegeben wurde
    const customAmountElement = document.getElementById('customAmount') as HTMLInputElement;
    if (customAmountElement && customAmountElement.value) {
      formData.customAmount = customAmountElement.value;
    }
    console.log("Individueller Betrag aus form:", formData.customAmount);
    if (this.isNewCustomer) {
      formData.firstName = (document.getElementById('NewfirstName') as HTMLInputElement)?.value;
      formData.lastName = (document.getElementById('NewlastName') as HTMLInputElement)?.value;

      // Erstelle neuen Kunden
      const newCustomerData = {
        vorname: formData.firstName,
        nachname: formData.lastName
      };
    fetch('http://localhost/Dr.Blech/backend/api/index.php/api/customers',{
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(newCustomerData)
    }).then(response => response.json())
    .then(data => {
      console.log('Kunde erfolgreich erstellt', data);
      this.numberCache = data.customerNumber; 
    })
    }
  
    console.log("data", formData); 
    fetch('http://localhost/Dr.Blech/backend/api/index.php/api/blech', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {

      const blechId = data.id; // Speichere die zurückgegebene ID
      console.log('POST Blech, ID:', blechId);
  
      // Sende einen weiteren POST-Request mit der Blech ID und der ausgewählten Kunden ID
      const customerElement = document.getElementById('existingCustomer') as HTMLSelectElement;
      if (customerElement) {
        const customerBlechData: any = {
          blechId: blechId,
          customerNumber: this.numberCache
        };
  
        // Füge den individuellen Betrag hinzu, falls vorhanden
        if (formData.customAmount) {
          customerBlechData.customAmount = formData.customAmount;
        }
        console.log(JSON.stringify(customerBlechData));
  
        fetch('http://localhost/Dr.Blech/backend/api/index.php/api/offers', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(customerBlechData)
        })
        .then(response => response.json())
        .then(data => {
          console.log('POST Offer, ID:', data.id);
          this.loadOffers(); 
          //location.reload(); 
        })
        .catch(error => {
          console.error('Fehler beim Zuordnen des Kunden', error);
        });
      } else {
        console.error('Kunden-Element nicht gefunden');
      }
    })
    .catch(error => {
      console.error('Fehler beim Erstellen des Angebots', error);
    });
  }}