package com.ecommerce.cod.model;

import com.ecommerce.cod.enums.MetodoPagamento;
import com.ecommerce.cod.enums.StatusPagamento;
import com.ecommerce.cod.enums.StatusPedido;
import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import org.hibernate.annotations.CreationTimestamp;
import org.hibernate.annotations.UpdateTimestamp;

import java.math.BigDecimal;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

@Entity
@Table(name = "pedidos")
@Data
@Builder
@NoArgsConstructor
@AllArgsConstructor
public class Pedido {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(name = "usuario_id", nullable = false)
    private Long usuarioId;

    @Column(name = "zona_id", nullable = false)
    private Long zonaId;

    @Column(name = "endereco_detalhado", nullable = false, length = 500)
    private String enderecoDetalhado;

    @Column(name = "ponto_referencia", length = 255)
    private String pontoReferencia;

    @Enumerated(EnumType.STRING)
    @Column(name = "metodo_pagamento", nullable = false)
    private MetodoPagamento metodoPagamento;

    @Column(name = "precisa_troco_para", precision = 10, scale = 2)
    private BigDecimal precisaTrocoPara;

    @Enumerated(EnumType.STRING)
    @Column(name = "status_pedido", nullable = false)
    @Builder.Default
    private StatusPedido statusPedido = StatusPedido.PENDENTE;

    @Enumerated(EnumType.STRING)
    @Column(name = "status_pagamento", nullable = false)
    @Builder.Default
    private StatusPagamento statusPagamento = StatusPagamento.PENDENTE;

    @Column(name = "total_produtos", nullable = false, precision = 10, scale = 2)
    @Builder.Default
    private BigDecimal totalProdutos = BigDecimal.ZERO;

    @Column(name = "total_entrega", nullable = false, precision = 10, scale = 2)
    @Builder.Default
    private BigDecimal totalEntrega = BigDecimal.ZERO;

    @Column(name = "valor_total", nullable = false, precision = 10, scale = 2)
    @Builder.Default
    private BigDecimal valorTotal = BigDecimal.ZERO;

    @Column(columnDefinition = "TEXT")
    private String observacoes;

    @Column(name = "criado_em", updatable = false)
    @CreationTimestamp
    private LocalDateTime criadoEm;

    @Column(name = "atualizado_em")
    @UpdateTimestamp
    private LocalDateTime atualizadoEm;

    @Transient
    @Builder.Default
    private List<ItemPedido> itens = new ArrayList<>();

    @PrePersist
    protected void onCreate() {
        criadoEm = LocalDateTime.now();
        atualizadoEm = LocalDateTime.now();
        calcularTotais();
    }

    @PreUpdate
    protected void onUpdate() {
        atualizadoEm = LocalDateTime.now();
        calcularTotais();
    }

    public void calcularTotais() {
        BigDecimal somaItens = itens.stream()
                .map(ItemPedido::getSubtotal)
                .reduce(BigDecimal.ZERO, BigDecimal::add);
        this.totalProdutos = somaItens;
        this.valorTotal = totalProdutos.add(totalEntrega);
    }

    public boolean precisaDeTroco() {
        return metodoPagamento == MetodoPagamento.DINHEIRO && precisaTrocoPara != null && precisaTrocoPara.compareTo(BigDecimal.ZERO) > 0;
    }

    public boolean podeSerCancelado() {
        return statusPedido == StatusPedido.PENDENTE;
    }

    public boolean podeSerEntregue() {
        return statusPedido == StatusPedido.A_CAMINHO || statusPedido == StatusPedido.PENDENTE;
    }
}
