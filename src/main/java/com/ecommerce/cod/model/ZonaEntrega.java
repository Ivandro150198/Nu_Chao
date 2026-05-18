package com.ecommerce.cod.model;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table(name = "zonas_entrega", uniqueConstraints = {
    @UniqueConstraint(name = "uk_nome_bairro", columnNames = "nome_bairro")
})
@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class ZonaEntrega {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "nome_bairro", nullable = false, length = 100)
    private String nomeBairro;

    @Column(name = "descricao_zona", length = 255)
    private String descricaoZona;

    @Column(name = "taxa_entrega", nullable = false, precision = 10, scale = 2)
    @Builder.Default
    private BigDecimal taxaEntrega = BigDecimal.ZERO;

    @Column(name = "tempo_estimado_entrega", length = 50)
    private String tempoEstimadoEntrega;

    @Column(nullable = false)
    @Builder.Default
    private Boolean ativa = true;

    @Column(name = "criado_em", updatable = false)
    @CreationTimestamp
    private LocalDateTime criadoEm;

    @Column(name = "atualizado_em")
    @UpdateTimestamp
    private LocalDateTime atualizadoEm;

    @PrePersist
    protected void onCreate() {
        criadoEm = LocalDateTime.now();
        atualizadoEm = LocalDateTime.now();
    }

    @PreUpdate
    protected void onUpdate() {
        atualizadoEm = LocalDateTime.now();
    }
}
